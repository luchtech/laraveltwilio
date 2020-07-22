<?php

namespace App\Http\Controllers;

use App\Recipient;
use Illuminate\Http\Request;

class RecipientController extends Controller
{
    private function itexmo($number, $message) {
        $apicode = getenv("ITEXMO_API_CODE");
        $passwd = getenv("ITEXMO_PASSWORD");
		$url = 'https://www.itexmo.com/php_api/api.php';
		$itexmo = array('1' => $number, '2' => $message, '3' => $apicode, 'passwd' => $passwd);
		$param = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($itexmo),
			),
		);
		$context  = stream_context_create($param);
		return file_get_contents($url, false, $context);
    }

    public function sendMessage($number, $message) {
        $result = $this->itexmo($number, $message);
        if ($result == "") {
            return back()->with(['error' => "No response from server!"]);
        }
    }

    /**
     * Send message to a selected users
     */
    public function sendCustomMessage(Request $request)
    {
        $validatedData = $request->validate([
            'recipients' => 'required|array',
            'textmessage' => 'required',
        ]);
        $recipients = $validatedData["recipients"];
        // iterate over the arrray of recipients and send a twilio request for each
        foreach ($recipients as $recipient) {
            $this->sendMessage($recipient, $validatedData["textmessage"]);
        }
        return back()->with(['success' => "Messages on their way!"]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $recipients = Recipient::all();
        return view('home', compact('recipients'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('addrecipient');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //run validation on data sent in
        $validatedData = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'phone' => 'required|unique:recipients|numeric',
        ]);
        $recipient = new Recipient($request->all());
        $recipient->save();
        $this->sendMessage($request->phone, 'User registration successful!');
        return back()->with(['success' => "Thank you for subscribing, {$recipient->firstname}! In the future, you will be notified with tsunami alerts."]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Recipient  $recipient
     * @return \Illuminate\Http\Response
     */
    public function show(Recipient $recipient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Recipient  $recipient
     * @return \Illuminate\Http\Response
     */
    public function edit(Recipient $recipient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Recipient  $recipient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Recipient $recipient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Recipient  $recipient
     * @return \Illuminate\Http\Response
     */
    public function destroy(Recipient $recipient)
    {
        //
    }
}
