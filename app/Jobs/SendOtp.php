<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Helper;

class SendOTP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $to, $body;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $to, $body )
    {
        $this->to = $to;
        $this->body = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = config( 'services.sms.sms_url' );

        $data['apikey'] = config( 'services.sms.api_id' );
        $data['secret'] = config( 'services.sms.api_key' );
        $data['content'] = $this->body;
        $data['mobile'] = $this->to;
        $data['template_id'] = config( 'services.sms.template_id' );

        $sendSMS = Helper::curlPost( config('services.sms.sms_url'), json_encode( $data ) );
    }
}
