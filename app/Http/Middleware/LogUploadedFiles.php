<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogUploadedFiles
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                Log::info('Uploaded file debug', [
                    'getPath' => method_exists($file, 'getPath') ? $file->getPath() : 'N/A',
                    'getPathname' => method_exists($file, 'getPathname') ? $file->getPathname() : 'N/A',
                    'getRealPath' => method_exists($file, 'getRealPath') ? $file->getRealPath() : 'N/A',
                    'getClientOriginalName' => $file->getClientOriginalName(),
                    'getSize' => $file->getSize(),
                    'isValid' => $file->isValid(),
                ]);
            }
        }
        
        return $next($request);
    }
}
