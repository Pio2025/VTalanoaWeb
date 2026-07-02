<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home/index');
    }

    public function pricing(): string
    {
        return view('home/pricing');
    }

    public function features(): string
    {
        return view('home/features');
    }

    public function terms(): string
    {
        return view('home/terms');
    }

    public function privacy(): string
    {
        return view('home/privacy');
    }

    public function contact(): string
    {
        return view('home/contact');
    }

    public function contactSubmit(): mixed
    {
        $rules = [
            'name'    => 'required|min_length[2]|max_length[120]',
            'email'   => 'required|valid_email',
            'subject' => 'required|min_length[4]|max_length[200]',
            'message' => 'required|min_length[20]|max_length[5000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name    = $this->request->getPost('name');
        $email   = $this->request->getPost('email');
        $subject = $this->request->getPost('subject');
        $message = $this->request->getPost('message');

        $sent = (new \App\Services\EmailService())->sendContactForm($name, $email, $subject, $message);

        if (!$sent) {
            return redirect()->back()->withInput()
                             ->with('error', 'We could not send your message. Please email us directly at support@navulifiji.com.');
        }

        return redirect()->to(base_url('contact'))->with('success', 'Thank you! Your message has been sent. We\'ll get back to you within 1–2 business days.');
    }
}
