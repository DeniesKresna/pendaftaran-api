<?php
 
namespace App\Mails;
 
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
 
class AcademyMail extends Mailable
{
    use Queueable, SerializesModels;
 
 
    /**
     * Create a new message instance.
     *
     * @return void
     */
    private $academyPaid;
    private $customerName;
    private $status;

    public function __construct($ap = [], $cn = "", $st = "")
    {
        $this->academyPaid = $ap;
        $this->customerName = $cn;
        $this->status = $st;
    }
 
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       return $this->from('info@jobhun.id')
                    ->subject("Payment Information")
                   ->view('mails/academy')
                   ->with(
                    [
                        'name' => $this->customerName,
                        'ja_list' =>  $this->academyPaid,
                        'status' =>  $this->status,
                    ]);
                   /*
                   ->attach(public_path('/hubungkan-ke-lokasi-file').'/demo.jpg', [
                      'as' => 'demo.jpg',
                      'mime' => 'image/jpeg',
                    ]);*/
    }
}