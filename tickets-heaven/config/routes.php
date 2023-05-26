<?php

use middleware\AuthMiddleware;
use middleware\HostMiddleware;
use controllers\HomeController;
use middleware\AdminMiddleware;
use middleware\GuestMiddleware;
use middleware\OwnerMiddleware;
use middleware\ArtistMiddleware;
use middleware\BeforeMiddleware;
use controllers\AuthController;
use controllers\DataTableController;
use controllers\UserController;
use controllers\PasswordController;
use Slim\Routing\RouteCollectorProxy;
use controllers\PanelController;
use controllers\EventController;
use controllers\VenueController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/', HomeController::class . ':index')->setName('home');

$app->get('/about', HomeController::class . ':about')->setName('about');

$app->get('/contact', HomeController::class . ':viewContact')->setName('contact');

$app->post('/contact', HomeController::class . ':postContact');

$app->get('/forbidden', HomeController::class . ':forbidden')->setName('forbidden');

$app->get('/events', EventController::class . ':getViewAllEvents')->setName('events.all');

$app->post('/events', EventController::class . ':postViewAllEvents');

$app->get('/event-details/{id}', EventController::class . ':getViewEventDetails')->setName('event.details');

$app->post('/event-details/{id}', EventController::class . ':postViewEventDetails');

$app->post('/events/comments/add', EventController::class . ':addEventComment')->setName('event.comments.add');

$app->get('/venues', VenueController::class . ':viewAllVenues')->setName('venues.all');

$app->get('/venue-details/{id}', VenueController::class . ':viewVenueDetails')->setName('venue.details');

$app->get('/venues/{username}', UserController::class . ':getOwnerVenues')->setName('venues.owner');

$app->get('/events/{username}', UserController::class . ':getHostEvents')->setName('events.host');

$app->get('/artists', UserController::class . ':viewAllArtists')->setName('artists.all');

$app->get('/artist-details/{username}', UserController::class . ':viewArtistDetails')->setName('artist.details');

$app->get('/hosts', UserController::class . ':viewAllHosts')->setName('hosts.all');

$app->get('/host-details/{username}', UserController::class . ':viewHostDetails')->setName('host.details');

$app->get('/owners', UserController::class . ':viewAllOwners')->setName('owners.all');

$app->get('/owner-details/{username}', UserController::class . ':viewOwnerDetails')->setName('owner.details');

$app->get('/cart', HomeController::class . ':getViewCart')->setName('cart');

$app->post('/cart', HomeController::class . ':postViewCart');

$app->post('/apply-promo', HomeController::class . ':postApplyPromoCode')->setName('promo.apply');

$app->post('/remove-promo', HomeController::class . ':postRemovePromoCode')->setName('promo.remove');

$app->group('', function($route) {

    $route->get('/register', AuthController::class . ':getRegister')->setName('register');

    $route->post('/register', AuthController::class . ':postRegister');

    $route->get('/activate', AuthController::class . ':getActivate')->setName('activate');

    $route->get('/login', AuthController::class . ':getLogin')->setName('login');

    $route->post('/login', AuthController::class . ':postLogin');

})->add(new GuestMiddleware($container));

$app->group('/password', function($route) use ($container) {

    $route->get('/recover', PasswordController::class . ':getRecoverPassword')->setName('password.recover')->add(new GuestMiddleware($container));

    $route->post('/recover', PasswordController::class . ':postRecoverPassword')->add(new GuestMiddleware($container));

    $route->get('/reset', PasswordController::class . ':getResetPassword')->setName('password.reset')->add(new GuestMiddleware($container));

    $route->post('/reset', PasswordController::class . ':postResetPassword')->add(new GuestMiddleware($container));

    $route->get('/change', PasswordController::class . ':getChangePassword')->setName('password.change')->add(new AuthMiddleware($container));

    $route->post('/change', PasswordController::class . ':postChangePassword')->add(new AuthMiddleware($container));
});

$app->group('/social', function($route) use ($container) {

    $route->group('/auth', function($subRoute) {

        $subRoute->post('/github', AuthController::class . ':postGitHubAuthentication')->setName('github.auth');

        $subRoute->post('/facebook', AuthController::class . ':postFacebookAuthentication')->setName('facebook.auth');
    });

    $route->group('/handle', function($subRoute) {

        $subRoute->get('/github', AuthController::class . ':handleGitHubAuthentication')->setName('github.handle');

        $subRoute->get('/facebook', AuthController::class . ':handleFacebookAuthentication')->setName('facebook.handle');
    });

    $route->group('/unlink', function($subRoute) {

        $subRoute->post('/github', AuthController::class . ':postGitHubUserUnlink')->setName('github.unlink');

        $subRoute->post('/facebook', AuthController::class . ':postFacebookUserUnlink')->setName('facebook.unlink');

    })->add(new AdminMiddleware($container));
});

$app->group('', function($route) {

    $route->get('/settings[/{username}]', UserController::class . ':getUserSettings')->setName('settings');

    $route->post('/settings[/{username}]', UserController::class . ':postUserSettings');

    $route->get('/profile', UserController::class . ':getUserProfile')->setName('profile');

    $route->post('/profile', UserController::class . ':postUserProfile');

    $route->get('/orders', UserController::class . ':getMyOrders')->setName('orders');

    $route->get('/orders/{id}', UserController::class . ':getOrderDetails')->setName('order.details');

    $route->get('/logout', AuthController::class . ':getLogout')->setName('logout');

})->add(new AuthMiddleware($container));

$app->group('/admin', function($route) {

    $route->get('', PanelController::class . ':getAdminPanel')->setName('admin.panel');

    $route->group('/venues', function($subRoute) {

        $subRoute->get('', VenueController::class . ':getVenues')->setName('admin.venues');

        $subRoute->get('/add', VenueController::class . ':getAddVenue')->setName('admin.venues.add');

        $subRoute->post('/add', VenueController::class . ':postAddVenue');

        $subRoute->get('/{id}', VenueController::class . ':getViewVenue')->setName('admin.venues.view');

        $subRoute->post('/{id}', VenueController::class . ':postViewVenue');
    });

    $route->group('/events', function($subRoute) {

        $subRoute->get('', EventController::class . ':getEvents')->setName('admin.events');

        $subRoute->get('/add', EventController::class . ':getAddEvent')->setName('admin.events.add');

        $subRoute->post('/add', EventController::class . ':postAddEvent');

        $subRoute->get('/{id}', EventController::class . ':getViewEvent')->setName('admin.events.view');

        $subRoute->post('/{id}', EventController::class . ':postViewEvent');
    });

    $route->group('/users', function($subRoute) {

        $subRoute->get('', UserController::class . ':getUsers')->setName('admin.users');

        $subRoute->get('/{username}', UserController::class . ':getViewUser')->setName('admin.users.view');

        $subRoute->post('/{username}', UserController::class . ':postViewUser');

        $subRoute->get('/{username}/orders', UserController::class . ':getUserOrders')->setName('admin.users.view.orders');

        $subRoute->get('/{username}/orders/{id}', UserController::class . ':getOrderDetails')->setName('admin.users.view.order.details');
    });

    $route->group('/support', function($subRoute) {

        $subRoute->get('', HomeController::class . ':getSupportTickets')->setName('admin.support');

        $subRoute->get('/{id}', HomeController::class . ':getViewSupportTicket')->setName('admin.support.view');

        $subRoute->post('/{id}', HomeController::class . ':postViewSupportTicket');
    });

    $route->group('/promotions', function($subRoute) {

        $subRoute->get('', HomeController::class . ':getPromoCodes')->setName('admin.promotions');

        $subRoute->get('/add', HomeController::class . ':getAddPromoCode')->setName('admin.promotions.add');

        $subRoute->post('/add', HomeController::class . ':postAddPromoCode');

        $subRoute->get('/{id}', HomeController::class . ':getViewPromoCode')->setName('admin.promotions.view');

        $subRoute->post('/{id}', HomeController::class . ':postViewPromoCode');
    });

})->add(new AdminMiddleware($container));

$app->group('/artist', function($route) {

    $route->get('', PanelController::class . ':getArtistPanel')->setName('artist.panel');

    $route->group('/events', function($subRoute) {

        $subRoute->get('', EventController::class . ':getMyEvents')->setName('artist.events');

        $subRoute->get('/inactive', EventController::class . ':getMyInactiveEvents')->setName('artist.events.inactive');

        $subRoute->get('/pending', EventController::class . ':getMyPendingEvents')->setName('artist.events.pending');

        $subRoute->post('/pending', EventController::class . ':postMyEvents');

        $subRoute->get('/approved', EventController::class . ':getMyApprovedEvents')->setName('artist.events.approved');

        $subRoute->post('/approved', EventController::class . ':postMyEvents');

        $subRoute->get('/rejected', EventController::class . ':getMyRejectedEvents')->setName('artist.events.rejected');

        $subRoute->post('/rejected', EventController::class . ':postMyEvents');
    });

})->add(new AuthMiddleware($container))->add(new ArtistMiddleware($container));

$app->group('/host', function($route) {

    $route->get('', PanelController::class . ':getHostPanel')->setName('host.panel');
    
    $route->group('/events', function($subRoute) {

        $subRoute->get('', EventController::class . ':getHostedEvents')->setName('host.events');

        $subRoute->get('/add', EventController::class . ':getAddHostEvent')->setName('host.events.add');

        $subRoute->post('/add', EventController::class . ':postAddHostEvent');

        $subRoute->get('/inactive', EventController::class . ':getInactiveHostedEvents')->setName('host.events.inactive');
    });

    $route->post('/events/{id}', EventController::class . ':postHostedEvents');

    $route->group('/promotions', function($subRoute) {

        $subRoute->get('', HomeController::class . ':getHostPromoCodes')->setName('host.promotions');

        $subRoute->get('/add', HomeController::class . ':getAddHostPromoCode')->setName('host.promotions.add');

        $subRoute->post('/add', HomeController::class . ':postAddHostPromoCode');

        $subRoute->get('/{id}', HomeController::class . ':getViewHostPromoCode')->setName('host.promotions.view');

        $subRoute->post('/{id}', HomeController::class . ':postViewHostPromoCode');
    });

})->add(new AuthMiddleware($container))->add(new HostMiddleware($container));

$app->group('/owner', function($route) {

    $route->get('', PanelController::class . ':getOwnerPanel')->setName('owner.panel');

    $route->group('/events', function($subRoute) {

        $subRoute->get('', EventController::class . ':getOwnerPendingEvents')->setName('owner.events.pending');

        $subRoute->post('', EventController::class . ':postOwnerEvents');

        $subRoute->get('/approved', EventController::class . ':getOwnerApprovedEvents')->setName('owner.events.approved');

        $subRoute->post('/approved', EventController::class . ':postOwnerEvents');

        $subRoute->get('/rejected', EventController::class . ':getOwnerRejectedEvents')->setName('owner.events.rejected');

        $subRoute->post('/rejected', EventController::class . ':postOwnerEvents');
    });

    $route->get('/venues', VenueController::class . ':getMyVenues')->setName('owner.venues');

    $route->post('/venues/{id}', VenueController::class . ':postMyVenues');

})->add(new AuthMiddleware($container))->add(new OwnerMiddleware($container));

$app->group('/datatable', function($route) {

    $route->post('/read', DataTableController::class . ':getTableJsonData');
    
    $route->post('/delete', DataTableController::class . ':deleteTableRowData');

    $route->post('/update', DataTableController::class . ':updateTableRowData');
});
