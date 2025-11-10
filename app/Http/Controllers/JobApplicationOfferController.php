<?php

namespace App\Http\Controllers;

use App\Enums\OfferStatus;
use App\Http\Requests\StoreOfferRequest;
use App\Models\JobApplication;
use App\Models\JobApplicationOffer;
use Illuminate\Http\Request;

class JobApplicationOfferController extends Controller
{
    /**
     * Show the form for creating a new offer.
     */
    public function create(JobApplication $application)
    {
        // Ensure user owns the application
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if offer already exists
        if ($application->offer) {
            return redirect()->route('job-applications.offers.edit', [$application, $application->offer])
                ->with('info', 'An offer already exists for this application. You can edit it below.');
        }

        $statuses = OfferStatus::cases();
        $currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN'];

        return view('job-applications.offers.create', compact('application', 'statuses', 'currencies'));
    }

    /**
     * Store a newly created offer.
     */
    public function store(StoreOfferRequest $request, JobApplication $application)
    {
        // Ensure user owns the application
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if offer already exists
        if ($application->offer) {
            return redirect()->route('job-applications.show', $application)
                ->with('error', 'An offer already exists for this application.');
        }

        $offer = $application->offer()->create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('job-applications.show', $application)
            ->with('success', 'Job offer recorded successfully!');
    }

    /**
     * Display the specified offer.
     */
    public function show(JobApplication $application, JobApplicationOffer $offer)
    {
        // Ensure user owns the offer
        if ($offer->user_id !== auth()->id() || $offer->job_application_id !== $application->id) {
            abort(403);
        }

        return view('job-applications.offers.show', compact('application', 'offer'));
    }

    /**
     * Show the form for editing the offer.
     */
    public function edit(JobApplication $application, JobApplicationOffer $offer)
    {
        // Ensure user owns the offer
        if ($offer->user_id !== auth()->id() || $offer->job_application_id !== $application->id) {
            abort(403);
        }

        $statuses = OfferStatus::cases();
        $currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN'];

        return view('job-applications.offers.edit', compact('application', 'offer', 'statuses', 'currencies'));
    }

    /**
     * Update the specified offer.
     */
    public function update(Request $request, JobApplication $application, JobApplicationOffer $offer)
    {
        // Ensure user owns the offer
        if ($offer->user_id !== auth()->id() || $offer->job_application_id !== $application->id) {
            abort(403);
        }

        $offer->update($request->all());

        return redirect()->route('job-applications.show', $application)
            ->with('success', 'Job offer updated successfully!');
    }

    /**
     * Remove the specified offer.
     */
    public function destroy(JobApplication $application, JobApplicationOffer $offer)
    {
        // Ensure user owns the offer
        if ($offer->user_id !== auth()->id() || $offer->job_application_id !== $application->id) {
            abort(403);
        }

        $offer->delete();

        return redirect()->route('job-applications.show', $application)
            ->with('success', 'Job offer deleted successfully!');
    }

    /**
     * Accept the offer.
     */
    public function accept(JobApplication $application, JobApplicationOffer $offer)
    {
        // Ensure user owns the offer
        if ($offer->user_id !== auth()->id() || $offer->job_application_id !== $application->id) {
            abort(403);
        }

        $offer->update(['status' => OfferStatus::ACCEPTED]);
        $application->update(['status' => \App\Enums\ApplicationStatus::ACCEPTED]);

        return back()->with('success', 'Offer accepted! Congratulations! 🎉');
    }

    /**
     * Decline the offer.
     */
    public function decline(JobApplication $application, JobApplicationOffer $offer)
    {
        // Ensure user owns the offer
        if ($offer->user_id !== auth()->id() || $offer->job_application_id !== $application->id) {
            abort(403);
        }

        $offer->update(['status' => OfferStatus::DECLINED]);

        return back()->with('success', 'Offer declined.');
    }
}
