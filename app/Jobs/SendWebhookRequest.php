<?php

namespace App\Jobs;

use App\Models\Department;
use App\Support\Encoders;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWebhookRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;
    protected $webhook;

    /**
     * Create a new job instance.
     */
    public function __construct($model, $webhook)
    {
        $this->model = $model;
        $this->webhook = $webhook;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $data = $this->model->getAttributes();
        $modelType = '';

        if (get_class($this->model) === "App\Models\Employee") {
            $data['user_id'] = optional($this->model->user)->email;
            $data['manager_id'] = optional($this->model->manager)->employee_code;
            $modelType = "employees";
        } elseif (get_class($this->model) === "App\Models\User") {
            $data['roles'] = $this->model->roles->pluck('id')->toArray();
            $modelType = "users";
        }
        elseif (get_class($this->model) === "App\Models\Designation") {
            $data['department_id']=Department::find($data['department_id'])?->name;
            $modelType = "designations";
        }
        elseif (get_class($this->model) === "App\Models\Department") {
            $data['parent_id'] = Department::find($data['parent_id'])?->name;
            $modelType = "departments";
        }


        $encryptData = Encoders::encrypt_request(json_encode($data), $this->webhook['secret-key']);
        $client = new Client();

        try {
            $response = $client->post($this->webhook['url'], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Secret' =>   $this->webhook['secret-header'],
                    'Model-Type' => $modelType
                ],
                'body' => $encryptData,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            info("Response status code: $statusCode, Response body: $responseBody");
            return;
        } catch (\Exception $e) {
            info($e->getMessage());
            return;
        }
    }
}
