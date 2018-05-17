<?php

namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use ExactivEM\User;
use Illuminate\Support\Facades\Auth;
use PhpImap\Mailbox as ImapMailbox;
use PhpImap\IncomingMail;
use ExactivEM\Config as SystemConfig;
use PhpImap\IncomingMailAttachment;
use Config;

class MailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
        
        $this->data['page']['title'] = 'Exactiv Email Client';

        //email client data and defaults
        $this->email_client = json_decode(Auth::user()->email_client_data);
        $this->email_client_defaults = json_decode(SystemConfig::find(27)->value);

        if(isset($this->email_client)){
            //create mail boxes on the server
            if(!is_dir( public_path('mailboxes/' . $this->email_client->email) ) ) {
                mkdir(public_path('mailboxes/' . $this->email_client->email));
            }

            $this->inboxPath = public_path('mailboxes/' . $this->email_client->email .'/INBOX');
            if(!is_dir($this->inboxPath) ){
                mkdir($this->inboxPath);
            }

            $this->draftPath = public_path('mailboxes/' . $this->email_client->email .'/DRAFT');
            if(!is_dir($this->draftPath) ){
                mkdir($this->draftPath);
            }

            $this->sentPath = public_path('mailboxes/' . $this->email_client->email .'/SENT');
            if(!is_dir($this->sentPath) ){
                mkdir($this->sentPath);
            }

            $this->trashPath = public_path('mailboxes/' . $this->email_client->email .'/TRASH');
            if(!is_dir($this->trashPath) ){
                mkdir($this->trashPath);
            }
        }
        
    }

    function index()
    {
        //check user restriction
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }

        //validates the configuration or redirect to setup
        if(!isset(Auth::user()->email_client_data)){
            return redirect('mail/setupMail');
        }

        //check only if we can connect to inbox
        $inbox = new ImapMailbox('{'.$this->email_client->imap_host.':'.$this->email_client->imap_port.'/imap/'.$this->email_client->encryption.'}INBOX', $this->email_client->email, $this->email_client->password, false);
        $mailsIds = $inbox->searchMailbox('ALL');
        //end checking

        return view('mails', $this->data);
    }   

    //folder selection
    function selectFolder($folder, $with_attachment = null){
        switch ($folder) {
            case 'Inbox':
                $this->data['mailbox'] = new ImapMailbox('{'.$this->email_client->imap_host.':'.$this->email_client->imap_port.'/imap/'.$this->email_client->encryption.'}INBOX', $this->email_client->email, $this->email_client->password, ($with_attachment?$this->inboxPath:false));
                $this->selectedFolder = 'INBOX';
            break;
            case 'Sent':
                $this->data['mailbox'] = new ImapMailbox('{'.$this->email_client->imap_host.':'.$this->email_client->imap_port.'/imap/'.$this->email_client->encryption.'}INBOX.Sent', $this->email_client->email, $this->email_client->password, ($with_attachment?$this->sentPath:false));
                 $this->selectedFolder = 'SENT';
            break;
            case 'Draft':
                $this->data['mailbox'] = new ImapMailbox('{'.$this->email_client->imap_host.':'.$this->email_client->imap_port.'/imap/'.$this->email_client->encryption.'}INBOX.Drafts', $this->email_client->email, $this->email_client->password, ($with_attachment?$this->draftPath:false));
                $this->selectedFolder = 'DRAFTS';
            break;
            case 'Trash':
                $this->data['mailbox'] = new ImapMailbox('{'.$this->email_client->imap_host.':'.$this->email_client->imap_port.'/imap/'.$this->email_client->encryption.'}INBOX.Trash', $this->email_client->email, $this->email_client->password, ($with_attachment?$this->trashPath:false));
                $this->selectedFolder = 'TRASH';
            break;
        }
    }

    //get mails in specific folder
    function getMails(Request $request){
        switch ($request->input('folder')) {
            case 'Inbox':
                $this->data['mailbox'] = new ImapMailbox('{'.$this->email_client->imap_host.':'.$this->email_client->imap_port.'/imap/'.$this->email_client->encryption.'}INBOX', $this->email_client->email, $this->email_client->password, false);
                $view = 'email_client.inbox';
            break;
            case 'Sent':
                $this->data['mailbox'] = new ImapMailbox('{'.$this->email_client->imap_host.':'.$this->email_client->imap_port.'/imap/'.$this->email_client->encryption.'}INBOX.Sent', $this->email_client->email, $this->email_client->password, false);
                $view = 'email_client.sent';
            break;
            case 'Draft':
                $this->data['mailbox'] = new ImapMailbox('{'.$this->email_client->imap_host.':'.$this->email_client->imap_port.'/imap/'.$this->email_client->encryption.'}INBOX.Drafts', $this->email_client->email, $this->email_client->password, false);
                $view = 'email_client.draft';
            break;
            case 'Trash':
                $this->data['mailbox'] = new ImapMailbox('{'.$this->email_client->imap_host.':'.$this->email_client->imap_port.'/imap/'.$this->email_client->encryption.'}INBOX.Trash', $this->email_client->email, $this->email_client->password,false);
                $view = 'email_client.trash';
            break;
        }
        
        //prepare mails
        $this->data['mails'] = $this->prepareMails($this->data['mailbox'], $request);
        //count mails
        $this->data['all_mails_count'] = sizeof($this->data['mailbox']->sortMails());

        //paginate
        $this->data['pages_count'] = ceil($this->data['all_mails_count']/10); 

        //return view with the data
        return view($view, $this->data);
    }

    function prepareMails($object, $params){
        //sortmails
        $mailsIds = $object->sortMails();
        $mails = array();

        //pagination config
        $current_page = $params->input('current_page');
        $pick_start = ($current_page-1) * 10 ;
        $pick_end = $pick_start + 9;

        //loop through each mails and set additional info
        foreach ($mailsIds as $key => $value) {

            if($pick_start<=$key AND $pick_end>=$key){
                //get info
                $mail = $object->getMailsInfo([$value])[0];
                $mails[] = array("subject"=> (isset($mail->subject)?$mail->subject:''),
                             "from"=>(isset($mail->from)?$mail->from:''),
                             "to"=>(isset($mail->to)?$mail->to:''),
                             "id"=>$mail->uid,
                             "flag"=>array("recent"=>$mail->recent,
                                           "flagged"=>$mail->flagged,
                                           "answered"=>$mail->answered,
                                           "deleted"=>$mail->deleted,
                                           "seen"=>$mail->seen,
                                           "draft"=>$mail->draft),
                             "date"=> date('Y-m-d H:i:s',$mail->udate));
            }

            
        }

        //return the mails data
        return $mails;
    }

    //function returns a single message
    function showMail(Request $request){
        //select folder
        $this->selectFolder($request->segment(3), true);
        $this->data['message'] = $this->data['mailbox']->getMail($request->segment(4));
        $this->data['message']->selectedFolder =$this->selectedFolder;

        //return view
        return view('email_client.message', $this->data);
    }

    //function to delete mail
    function deleteMail(Request $request){
        $this->selectFolder($request->input('folder'));
        foreach ($request->input('ids') as $key => $value) {
           $this->data['mailbox']->deleteMail($value);
        }

        //success message
        return 'okay';
    }

    //function to addFlag
    function addFlag(Request $request){
        $this->selectFolder($request->input('folder'));
        foreach ($request->input('ids') as $key => $value) {
           $this->data['mailbox']->setFlag([$value], '\\' . $request->input('flag'));
        }
        //success message
        return 'okay';
    }

    //function to clearFlag on the mail
    function clearFlag(Request $request){
        $this->selectFolder($request->input('folder'));
        foreach ($request->input('ids') as $key => $value) {
           $this->data['mailbox']->clearFlag([$value], '\\' . $request->input('flag'));
        }
        //success message
        return 'okay';
    }

    //function to moveMail
   function moveMail(Request $request){
        $this->selectFolder($request->input('folder'));

        foreach ($request->input('ids') as $key => $value) {
            $dest = ($request->input('destination')=='Inbox'? 'INBOX':'INBOX.'.$request->input('destination'));
            $this->data['mailbox']->moveMail($value, $dest);
        }
        //success message
        return 'okay';
    }

    //returns setupMail Page
    function setupMail(){
        $this->data['email_client'] = $this->email_client;
        $this->data['email_client_defaults'] = $this->email_client_defaults;

        //returns view
        return view('setupmail', $this->data);
    }

    //function to edit mail configuration
    function processEdit(Request $request){
        $user = User::find(Auth::user()->id);
        $data = array("imap_host"=>$request->input('imap_host'),
                      "imap_port"=>$request->input('imap_port'),
                      "smtp_host"=>$request->input('smtp_host'),
                      "smtp_port"=>$request->input('smtp_port'),
                      "encryption"=>$request->input('encryption'),
                      "email"=>$request->input('email'),
                      "password"=>$request->input('password'));
        //json data
        $user->email_client_data = json_encode($data);
        $user->save();

        //redirect to setup mail with success message
        return redirect('mail/setupMail')->with('update','success');
    }

    //function that sends mail using SMTP
    //uses PHPMailer Library
    function sendMail(Request $request){
        $mail = new \PHPMailer(true); // notice the \  you have to use root namespace here
        try {

            $mail->isSMTP(); // tell to use smtp
            $mail->CharSet = "utf-8"; // set charset to utf8
            $mail->SMTPAuth = true;  // use smpt auth
            $mail->SMTPSecure = "tls"; // or ssl
            $mail->Host = $this->email_client->smtp_host;
            $mail->Port = $this->email_client->smtp_port; // most likely something different for you. This is the mailtrap.io port i use for testing. 
            $mail->Username = $this->email_client->email;
            $mail->Password = $this->email_client->password;
            $mail->setFrom($this->email_client->email, Auth::user()->first_name .' ' . Auth::user()->last_name);
            $mail->Subject = $request->input('subject');
            $mail->MsgHTML($request->input('message'));

            //setup to,cc,bcc receipients
            foreach ($request->input('to') as $key => $value) {
                 $mail->addAddress($value);
            }
           
            if(!empty($request->input('cc'))){
                foreach ($request->input('cc') as $key => $value) {
                     $mail->addCC($value);
                }
            }

            if(!empty($request->input('bcc'))){
                foreach ($request->input('bcc') as $key => $value) {
                     $mail->addBCC($value);
                }
            }

            //add files if available
            if(!empty($request->input('files'))){
                foreach ($request->input('files') as $key => $value) {
                     $mail->addAttachment(public_path('jquery-uploader/php/files/'.$value));
                }
            }

            //send mail via SMTP Protocol
            $mail->send();
            $stream = imap_open('{'.$this->email_client->imap_host.':'.$this->email_client->imap_port.'/imap/'.$this->email_client->encryption.'}INBOX.Sent', $this->email_client->email, $this->email_client->password);

            //save to sent items
            imap_append($stream, "{". $this->email_client->imap_host ."/".$this->email_client->encryption."}INBOX.Sent",$mail->getSentMIMEMessage(), "\\Seen");
            
            //success message
            echo 'ok';

        } 
        catch (phpmailerException $e) {
            //error handler
            dd($e);
        } 
        catch (Exception $e) {
            //error handler
            dd($e);
        }
    }
}

