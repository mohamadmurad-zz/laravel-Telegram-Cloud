<?php

namespace App\Http\Controllers;

use App\Models\files;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramResponseException;

class FilesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        $files = files::all();

        return view('files/index', compact('files'));
    }

    public function setChatID()
    {

        $telegram = new Api(env('TELEGRAM_TOKEN'));
        $updates = $telegram->getUpdates();

        if (count($updates)){
            config(['telegram.chat_id' => $updates[0]->message->chat->id]);


            dd(config('telegram.chat_id'));
        }
        return 'Please send an message to your bot';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('files/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     */
    public function store(Request $request)
    {


        $this->validate($request, [
            'file' => 'required|max:5000'
        ]);


        $telegram = new Api(env('TELEGRAM_TOKEN'));

        $files = \Telegram\Bot\FileUpload\InputFile::createFromContents($request->file('file')->getContent(), $request->file('file')->getClientOriginalName());


        $response = $telegram->sendDocument([
            'chat_id' => config('telegram.chat_id'),
            'document' => $files,
            'caption' => 'This is a document',
        ]);


        if (!$response->message_id) {
            return redirect()->route('files.index')->with('error', 'error Upload');
        }

        $file_thumb_path = null;

        if ($response->document->thumb) {
            $file_thumb_path = $this->getFilePath($response->document->thumb->file_id);
        }

        $newFile = files::create([
            'file_id' => $response->document->file_id,
            'file_name' => $response->document->file_name,
            'mime_type' => $response->document->mime_type,
            'file_thumb_path' => $file_thumb_path,
            'message_id' => $response->message_id,
            'chat_id' => $response->chat->id,
        ]);


        return redirect()->route('files.index')->with('success', 'Uploaded');
    }

    public function download($id)
    {


        $file = files::where('file_id', $id)->first();
        if (!$file) {

            return redirect()->route('files.index')->with('error', 'No File Found');
        }
        $path = $this->getFilePath($id);

        if ($path) {

            $base = file_get_contents('https://api.telegram.org/file/bot' . env('TELEGRAM_TOKEN') . '/' . $path);


            $headers = [
                'Content-type' => $file->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $file->file_name . '"',
            ];
            return Response::make($base, 200, $headers);

        }

        return redirect()->route('files.index')->with('error', 'Error in Download');


    }

    private function getFilePath($file_id)
    {

        $response = Http::get('https://api.telegram.org/bot' . env('TELEGRAM_TOKEN') . '/getFile?file_id=', [
            'file_id' => $file_id,
        ])->json();

        if (!$response['ok']) {
            return null;
        }

        return $response['result']['file_path'] ?? null;

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $file = files::where('file_id', $id)->first();
        if (!$file) {

            return redirect()->route('files.index')->with('error', 'No File Found');
        }
        $telegram = new Api(env('TELEGRAM_TOKEN'));

        try {
            $response = $telegram->deleteMessage([
                'message_id' => $file->message_id,
                'chat_id' => $file->chat_id,
            ]);


            if ($response) {
                $file->delete();
                return redirect()->route('files.index')->with('success', 'File deleted');
            }


        } catch (TelegramResponseException $e) {
            return redirect()->route('files.index')->with('error', 'Error File not deleted');

        }

        return redirect()->route('files.index')->with('error', 'Error File not deleted');


    }
}
