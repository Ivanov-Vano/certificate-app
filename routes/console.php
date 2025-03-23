<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:parse-lead-emails REC')->everyFifteenMinutes();
