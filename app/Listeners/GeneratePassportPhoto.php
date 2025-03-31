<?php

namespace App\Listeners;

use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GeneratePassportPhoto implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent  $event
     * @return void
     */
    public function handle(MediaHasBeenAddedEvent $event)
    {
        $media = $event->media;
        if ($media->collection_name == "employee_photo") {
            Log::info("Passport Photo Generation for Employee #{$media->model->id} started ...");
            if ($media->disk != 'local') {
                $client = new Client();
                $media_url = $media->getUrl();
                $file_extension = pathinfo(parse_url($media_url, PHP_URL_PATH), PATHINFO_EXTENSION);
                $content = $client->get($media_url)->getBody()->getContents();
            } else {
                $media_path = $media->getPath();
                $file_extension = pathinfo($media_path, PATHINFO_EXTENSION);
                Log::info($media_path);
                $content = file_get_contents($media_path);
            }

            $temp_file_name = uniqid('input_photo_') . '.' . $file_extension;
            Storage::disk('local')->put($temp_file_name, $content);
            $temp_media_path = Storage::disk('local')->path($temp_file_name);

            $passport_photo_media_file_name = uniqid('passport_photo_') . '.png';
            Storage::disk('local')->put($passport_photo_media_file_name, '');
            $passport_photo_media_path = Storage::disk('local')->path($passport_photo_media_file_name);

            $command = base_path('tools/id-card-creator/passport_photo_creator');
            $arguments = ['-i', $temp_media_path, '-o', $passport_photo_media_path, '-r', $media->model->head_to_face_ratio];

            $process = new Process([$command, ...$arguments]);

            // Specify a timeout if needed (null means no timeout)
            $process->setTimeout(null);

            // Run the command
            try {
                $process->mustRun();

                // Return the output of the command if needed

                $media->model->addMedia($passport_photo_media_path)->toMediaCollection('passport_photo');

                Log::info($process->getOutput());
                Log::info("Passport Photo Generated at $passport_photo_media_path ...");
                Log::info("Cleaning temporary files {$temp_file_name} and {$passport_photo_media_file_name}...");
                Storage::disk('local')->delete($temp_file_name);
                Storage::disk('local')->delete($passport_photo_media_file_name);
                Log::info("All Done!");
            } catch (ProcessFailedException $exception) {
                // Handle the error
                Log::error($exception->getMessage());
            }
        }
    }
}
