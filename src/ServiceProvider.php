<?php

namespace ProtoneMedia\AnalyticsEventTracking;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use ProtoneMedia\AnalyticsEventTracking\Analytics\BroadcastEvent;
use ProtoneMedia\AnalyticsEventTracking\Analytics\EventBroadcaster;
use ProtoneMedia\AnalyticsEventTracking\Http\ClientIdRepository;
use ProtoneMedia\AnalyticsEventTracking\Http\ClientIdSession;
use ProtoneMedia\AnalyticsEventTracking\Http\SessionIdRepository;
use ProtoneMedia\AnalyticsEventTracking\Http\SessionIdSession;
use ProtoneMedia\AnalyticsEventTracking\Http\StoreClientIdInSession;
use ProtoneMedia\AnalyticsEventTracking\Http\StoreSessionIdInSession;
use ProtoneMedia\AnalyticsEventTracking\Listeners\DispatchAnalyticsJob;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'analytics-event-tracking');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('analytics-event-tracking.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/analytics-event-tracking'),
            ], 'views');
        }

        Event::listen(ShouldBroadcastToAnalytics::class, DispatchAnalyticsJob::class);

        Blade::directive('sendAnalyticsClientId', function () {
            return "<?php echo view('analytics-event-tracking::sendCliendId'); ?>";
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php',
            'analytics-event-tracking'
        );

        $this->app->singleton(EventBroadcaster::class, BroadcastEvent::class);

        $this->registerClientId();
        $this->registerAnalytics();
        $this->registerRoute();
    }

    private function registerClientId()
    {
        $this->app->singleton(ClientIdRepository::class, ClientIdSession::class);

        $this->app->bind('analytics-event-tracking.client-id', function () {
            return $this->app->make(ClientIdSession::class)->get();
        });

        $this->app->singleton(ClientIdSession::class, function () {
            return new ClientIdSession(
                $this->app->make('session.store'),
                config('analytics-event-tracking.client_id_session_key')
            );
        });

        $this->app->singleton(SessionIdRepository::class, SessionIdSession::class);

        $this->app->bind('analytics-event-tracking.session-id', function () {
            return $this->app->make(SessionIdSession::class)->get();
        });

        $this->app->singleton(SessionIdSession::class, function () {
            return new SessionIdSession(
                $this->app->make('session.store'),
                config('analytics-event-tracking.session_id_session_key')
            );
        });
    }

    public function registerAnalytics()
    {
        $this->app->bind(Analytics::class, function () {
            return tap(new Analytics(config('analytics-event-tracking.use_ssl'), !config('analytics-event-tracking.is_enabled', true)), function (Analytics $analytics) {
                $analytics->setProtocolVersion(config('analytics-event-tracking.protocol_version'))->setTrackingId(
                    config('analytics-event-tracking.tracking_id')
                );

                if (config('analytics-event-tracking.anonymize_ip')) {
                    $analytics->setAnonymizeIp(1);
                }
            });
        });
    }

    private function registerRoute()
    {
        if ($httpUri = config('analytics-event-tracking.http_uri_cid')) {
            Route::post($httpUri, [StoreClientIdInSession::class, '__invoke'])->middleware('web');
        }
        if ($httpUri = config('analytics-event-tracking.http_uri_sid')) {
            Route::post($httpUri, [StoreSessionIdInSession::class, '__invoke'])->middleware('web');
        }
    }
}
