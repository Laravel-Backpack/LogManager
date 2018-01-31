<?php

namespace Backpack\LogManager\app\Http\Controllers;

use Backpack\LogManager\app\Classes\LogViewer;
use Illuminate\Routing\Controller;

class LogController extends Controller
{
    /**
     * Lists all log files.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->data['files'] = LogViewer::getFiles(true);
        $this->data['title'] = trans('backpack::logmanager.log_manager');

        return view('logmanager::logs', $this->data);
    }

    /**
     * Previews a log file.
     *
     * @throws \Exception
     */
    public function preview($file_name)
    {
        LogViewer::setFile(base64_decode($file_name));

        $logs = LogViewer::all();
        dd($logs);
        if(count($logs) <= 0) {
            abort(404, trans('backpack::logmanager.log_file_doesnt_exist'));
        }

        $this->data['logs'] = $logs;
        $this->data['title'] = trans('backpack::logmanager.preview').' '.trans('backpack::logmanager.logs');
        $this->data['file_name'] = base64_decode($file_name);

        return view('logmanager::log_item', $this->data);
    }

    /**
     * Downloads a log file.
     *
     * @param $file_name
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \Exception
     */
    public function download($file_name)
    {
        return response()->download(LogViewer::pathToLogFile(base64_decode($file_name)));
    }

    /**
     * Deletes a log file.
     *
     * @param $file_name
     * @return string
     *
     * @throws \Exception
     */
    public function delete($file_name)
    {
        if(app('files')->delete(LogViewer::pathToLogFile(base64_decode($file_name)))) {
            return 'success';
        }

        abort(404, trans('backpack::logmanager.log_file_doesnt_exist'));
    }
}
