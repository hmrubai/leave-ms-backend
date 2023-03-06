<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PHPMailer\PHPMailer\PHPMailer;  
use PHPMailer\PHPMailer\Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function checkEmailSending(Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'email' => 'required',
            'body' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateUser->errors()
            ], 409);
        }

        $result = $this->sendTestEmail($request->email, $request->body);

        return response()->json([
            'status' => true,
            'message' => $result,
            'data' => []
        ], 200);
    }

    public function sendEmailForLeave($recipants_emails, $body)
    {
        $email_body = $this->generateNewLeaveMailBody($body);
        $this->sendCommonEmail($recipants_emails, $email_body);
        return true;
    }

    public function sendCommonEmail($recipants_emails, $body) {
        require base_path("vendor/autoload.php");
        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = 0;
            //$mail->isSMTP();
            $mail->Host = env('BB_MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('BB_MAIL_USERNAME');
            $mail->Password = env('BB_MAIL_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port = env('BB_MAIL_PORT'); 

            $mail->setFrom(env('BB_MAIL_FROM_ADDRESS'), env('BB_MAIL_FROM_NAME'));
            $mail->addAddress('bacbonleave@gmail.com');

            foreach ($recipants_emails as $email) {
                $mail->addAddress($email);
            }

            $mail->addReplyTo(env('BB_MAIL_FROM_ADDRESS'), env('BB_MAIL_FROM_NAME'));

            $mail->isHTML(true);

            $mail->Subject = 'Leave Application - BacBon Support';
            $mail->Body    = $this->prepareEmailTemplate($body);

            if( !$mail->send() ) {
                return "Mailer Send Failed";
            }
            return true;

        } catch (Exception $e) {
            return false;
            return "Mailer Send - Exception";
        }
    }

    public function sendTestEmail($recipants_emails, $body) {
        require base_path("vendor/autoload.php");
        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = 2;
            $mail->Host = env('BB_MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('BB_MAIL_USERNAME');
            $mail->Password = env('BB_MAIL_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port = env('BB_MAIL_PORT'); 

            $mail->setFrom(env('BB_MAIL_FROM_ADDRESS'), env('BB_MAIL_FROM_NAME'));

            $mail->addAddress($recipants_emails);

            $mail->addReplyTo(env('BB_MAIL_FROM_ADDRESS'), env('BB_MAIL_FROM_NAME'));

            $mail->isHTML(true);
            $mail->Subject = 'Leave Application - BacBon Support';
            $template = $this->prepareEmailTemplate($this->generateTestMailBody());
            $mail->Body = $template;

            if( !$mail->send() ) {
                return "Mailer Send Failed";
            }
            return "Mailer Send Successful";

        } catch (Exception $e) {
            return "Mailer Send - Exception";
        }
    }

    public function generateTestMailBody(){

        $html = "<h4>A leave application has been sent. Please, check below details: </h4>";

        $html = $html . "<h4>Application Details: </h4>
        <table width='100%' style='border:1px solid #eee;'>
            <tr>
                <td style='width:25%'>Applicant's Name: </td>
                <td><strong>" . "Hosne Mobraka Rubai" . "</strong></td>
            </tr>
            <tr>
                <td>Designation:</td>
                <td>" . "Software Engineer" . "</td>
            </tr>
            <tr>
                <td>Department:</td>
                <td>" . "IT & Software Development" . "</td>
            </tr>
            <tr>
                <td>Leave Type:</td>
                <td><strong>" . "Casual Leave" . "</strong></td>
            </tr>
            <tr>
                <td>Start date:</td>
                <td><strong>" . "33/03/2023" . "</strong></td>
            </tr>
            <tr>
                <td>End date:</td>
                <td><strong>" . "34/03/2023" . "</strong></td>
            </tr>
            <tr>
                <td>Appled For:</td>
                <td><strong>" . "2" . " Days</strong></td>
            </tr>
        </table><br/> Thanks. <br/>";

        return $html;
    }

    public function generateNewLeaveMailBody($body){

        $html = "<h4 style='font-size:15px;margin:0 0 10px 0;font-family:Arial,sans-serif;'>A leave application has been sent. Please, check below details: </h4>";

        $html = $html . "<h4 style='font-size:15px;margin:0 0 10px 0;font-family:Arial,sans-serif;'>Application Details: </h4>
        <table width='100%' style='border:1px solid #eee;'>
            <tr>
                <td style='width:25%'>Applicant's Name: </td>
                <td><strong>" . $body['applicant_name'] . "</strong></td>
            </tr>
            <tr>
                <td>Designation:</td>
                <td>" . $body['designation'] . "</td>
            </tr>
            <tr>
                <td>Department:</td>
                <td>" . $body['department'] . "</td>
            </tr>
            <tr>
                <td>Leave Type:</td>
                <td><strong>" . $body['leave_type'] . "</strong></td>
            </tr>
            <tr>
                <td>Start date:</td>
                <td><strong>" . $body['start_date'] . "</strong></td>
            </tr>
            <tr>
                <td>End date:</td>
                <td><strong>" . $body['end_date'] . "</strong></td>
            </tr>
            <tr>
                <td>Appled For:</td>
                <td><strong>" . $body['total_days'] . " Days</strong></td>
            </tr>
        </table><br/> Thanks. <br/>";

        return $html;
    }

    public function generateApprovedLeaveMailBody($body){

        $html = "<h4 style='font-size:15px;margin:0 0 10px 0;font-family:Arial,sans-serif;'>A leave application has been <span style='color:#006400; font-weight:bold;'>approved</span>. Please, check below details: </h4>";

        $html = $html . "<h4 style='font-size:15px;margin:0 0 10px 0;font-family:Arial,sans-serif;'>Application Details: </h4>
        <table width='100%' style='border:1px solid #eee;'>
            <tr>
                <td style='width:25%'>Applicant's Name: </td>
                <td><strong>" . $body['applicant_name'] . "</strong></td>
            </tr>
            <tr>
                <td>Designation:</td>
                <td>" . $body['designation'] . "</td>
            </tr>
            <tr>
                <td>Department:</td>
                <td>" . $body['department'] . "</td>
            </tr>
            <tr>
                <td>Leave Type:</td>
                <td><strong>" . $body['leave_type'] . "</strong></td>
            </tr>
            <tr>
                <td>Start date:</td>
                <td><strong>" . $body['start_date'] . "</strong></td>
            </tr>
            <tr>
                <td>End date:</td>
                <td><strong>" . $body['end_date'] . "</strong></td>
            </tr>
            <tr>
                <td>Appled For:</td>
                <td><strong>" . $body['total_days'] . " Days</strong></td>
            </tr>
        </table><br/> Thanks. <br/>";

        return $html;
    }

    public function generateRejectedLeaveMailBody($body){

        $html = "<h4 style='font-size:15px;margin:0 0 10px 0;font-family:Arial,sans-serif;'>A leave application has been <span style='color:#8B0000; font-weight:bold;'>Rejected</span>. Please, check below details: </h4>";

        $html = $html . "<h4 style='font-size:15px;margin:0 0 10px 0;font-family:Arial,sans-serif;'>Application Details: </h4>
        <table width='100%' style='border:1px solid #eee;'>
            <tr>
                <td style='width:25%'>Applicant's Name: </td>
                <td><strong>" . $body['applicant_name'] . "</strong></td>
            </tr>
            <tr>
                <td>Designation:</td>
                <td>" . $body['designation'] . "</td>
            </tr>
            <tr>
                <td>Department:</td>
                <td>" . $body['department'] . "</td>
            </tr>
            <tr>
                <td>Leave Type:</td>
                <td><strong>" . $body['leave_type'] . "</strong></td>
            </tr>
            <tr>
                <td>Start date:</td>
                <td><strong>" . $body['start_date'] . "</strong></td>
            </tr>
            <tr>
                <td>End date:</td>
                <td><strong>" . $body['end_date'] . "</strong></td>
            </tr>
            <tr>
                <td>Appled For:</td>
                <td><strong>" . $body['total_days'] . " Days</strong></td>
            </tr>
        </table><br/> Thanks. <br/>";

        return $html;
    }

    public function prepareEmailTemplate($details = null){
        $email_body = '<!DOCTYPE html>
        <html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <meta name="x-apple-disable-message-reformatting">
            <title>Leave Application - BacBon Support</title>
            <style>
                td,
                div,
                h1,
                p {
                    font-family: Arial, sans-serif;
                }
                .body_style > table {
                    font-family: Arial, sans-serif;
                    border-collapse: collapse;
                    width: 100%;
                }
                .body_style table td, th {
                    border: 1px solid #dddddd;
                    text-align: left;
                    padding: 8px;
                }
                .body_style table tr:nth-child(even) {
                    background-color: #dddddd;
                }
            </style>
        </head>
        <body style="margin:0;padding:0;">
            <table role="presentation"
                style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
                <tr>
                    <td align="center" style="padding:0;">
                        <table role="presentation"
                            style="width:602px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">
                            <tr>
                                <td align="center" style="padding:0px 0;background:#006abf;">
                                    <img src="http://api-leavems.bacbonschool.com/uploads/company_image/t_bacbon_logo.png" alt="" width="50%" style="height:auto;display:block;padding: 20px;" />
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:36px 30px 42px 30px;">
                                    <table role="presentation" class="body_style"
                                        style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
                                        <tr>
                                            <td style="padding:0 0 0px 0;color:#153643;">
                                                <h2 style="font-size:15px;margin:0 0 10px 0;font-family:Arial,sans-serif;">
                                                    Dear Concern,
                                                </h2>
                                                <h1 style="font-size:14px;margin:0 0 10px 0;font-family:Arial,sans-serif;font-weight: 300;">
                                                    ' . $details . '
                                                </h1>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:30px;background:#0087f3;">
                                    <table role="presentation"
                                        style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">
                                        <tr>
                                            <td style="padding:0;width:50%;" align="left">
                                                <p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">
                                                    &copy; BacBon Limited 2023
                                                </p>
                                            </td>
                                            <td style="padding:0;width:50%;" align="right">
                                                <table role="presentation"
                                                    style="border-collapse:collapse;border:0;border-spacing:0;">
                                                    <tr>
                                                        <td style="padding:0 0 0 10px;width:38px;">
                                                            <a href="http://www.twitter.com/" style="color:#ffffff;"><img
                                                                    src="https://assets.codepen.io/210284/tw_1.png"
                                                                    alt="Twitter" width="38"
                                                                    style="height:auto;display:block;border:0;" /></a>
                                                        </td>
                                                        <td style="padding:0 0 0 10px;width:38px;">
                                                            <a href="http://www.facebook.com/BacBonLimited" style="color:#ffffff;"><img
                                                                    src="https://assets.codepen.io/210284/fb_1.png"
                                                                    alt="Facebook" width="38"
                                                                    style="height:auto;display:block;border:0;" /></a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
        return $email_body;
    }
}
