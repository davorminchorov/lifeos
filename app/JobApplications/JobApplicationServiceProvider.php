<?php

namespace App\JobApplications;

use App\JobApplications\Commands\RecordOutcomeCommand;
use App\JobApplications\Commands\RecordOutcomeHandler;
use App\JobApplications\Commands\ScheduleInterviewCommand;
use App\JobApplications\Commands\ScheduleInterviewHandler;
use App\JobApplications\Commands\SubmitApplicationCommand;
use App\JobApplications\Commands\SubmitApplicationHandler;
use App\JobApplications\Commands\UpdateApplicationCommand;
use App\JobApplications\Commands\UpdateApplicationHandler;
use Illuminate\Support\ServiceProvider;

class JobApplicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register command handlers
        $this->app->bind(SubmitApplicationHandler::class);
        $this->app->bind(UpdateApplicationHandler::class);
        $this->app->bind(ScheduleInterviewHandler::class);
        $this->app->bind(RecordOutcomeHandler::class);

        // Command to handler bindings
        $this->app->bindMethod([SubmitApplicationCommand::class, 'handle'], function ($command, $app) {
            return $app->make(SubmitApplicationHandler::class)->handle($command);
        });

        $this->app->bindMethod([UpdateApplicationCommand::class, 'handle'], function ($command, $app) {
            return $app->make(UpdateApplicationHandler::class)->handle($command);
        });

        $this->app->bindMethod([ScheduleInterviewCommand::class, 'handle'], function ($command, $app) {
            return $app->make(ScheduleInterviewHandler::class)->handle($command);
        });

        $this->app->bindMethod([RecordOutcomeCommand::class, 'handle'], function ($command, $app) {
            return $app->make(RecordOutcomeHandler::class)->handle($command);
        });
    }

    public function boot(): void
    {
        // Register any boot actions here
    }
}
