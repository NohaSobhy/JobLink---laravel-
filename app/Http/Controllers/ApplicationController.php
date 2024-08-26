<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\Candidate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class ApplicationController extends Controller
{
    //
    public function create(JobPosting $job)
    {
        $user = Auth::user();
        $candidate = Candidate::where('user_id', $user->id)->first();
        return view('candidates.apply', compact('job','candidate'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'job_posting_id' => 'required|exists:job_postings,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:15',
            'cv' => 'required|mimes:pdf,doc,docx|max:2048',
        ]);

        // $user = auth()->user();
        // $user->name = $request->name;
        // $user->email = $request->email;
        // $user->save();


        // $candidate = $user->candidate;
        // $candidate->phone_number = $request->phone;
        // $candidate->save();


        $cvPath = $request->file('cv')->store('cvs', 'public');


        Application::create([
            'candidate_id' => $request->candidate_id,
            'job_posting_id' => $request->job_posting_id,
            'status' => 'pending',
            'cv' => $cvPath,
        ]);

        return redirect()->route('jobs.index')->with('success', 'Your application has been submitted successfully!');
    }

    public function destroy($id)
    {
        $application = Application::findOrFail($id);
        $user = Auth::user();
        $candidate = Candidate::where('user_id', $user->id)->first();
        if (!$candidate) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $application->delete();
    
        return redirect()->route('candidate_applications')->with('success', 'Application deleted successfully.');
    }
    


}
