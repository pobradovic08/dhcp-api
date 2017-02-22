<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/7/2017
 * Time: 5:51 PM
 */

$app->get('/test[/]', function ($request, $response, $args) use ($app) {
    $cap = $app->getContainer()->capsule;
    $m = \Dhcp\Model\EndHostModel::with('reservations')->findOrFail(32);
    $t = \Dhcp\Model\EndHostTypeModel::with('endhosts')->get();
    $t = \Dhcp\Model\EndHostTypeModel::find(1);
    $r = \Dhcp\Model\ReservationModel::with('group', 'end_host')->get();
    $r = \Dhcp\Model\ReservationModel::findOrFail(8);
    $g = \Dhcp\Model\GroupModel::with('subnet', 'reservations')->get();
    $s = \Dhcp\Model\SubnetModel::with('reservations')->find(3);
    $m = \Dhcp\Model\EndHostModel::where($cap::raw('HEX(mac)'), 'LIKE', '%74D4359ADF%')->get();

    return $response->withJson($r->safeToInsert());
});

$app->put('/test[/]', function ($request, $response, $args) use ($app) {
    $app->getContainer()->capsule;
    $t = \Dhcp\EndHostType\EndHostTypeModel::findOrCreate($request->getParam('end_host_type_id'));
    $t->description = 'aaaaaaaaaaa';
    $t->save();
    return $response->withJson($t);
});

/*
 * End Hosts
 */

$app->group('/endhosts', function () use ($app) {
    /* Get all End Hosts */
    $app->get('[/all]', 'EndHostController:get_host');
    /* Get, update or delete end host by ID */
    $app->get('/id/{end_host_id:[0-9]+}[/]', 'EndHostController:get_host_by_id');
    $app->put('/id/{end_host_id:[0-9]+}[/]', 'EndHostController:update_host');
    $app->delete('/id/{end_host_id:[0-9]+}[/]', 'EndHostController:delete_host');
    /* Get end host by MAC address */
    $app->get('/mac/{mac:(?:(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})}[/]',
              'EndHostController:get_host_by_mac');
    /* Search host */
    $app->get('/search/{pattern}[/]', 'EndHostController:get_search_host');
    /* Create or update new end host */
    $app->post('[/new]', 'EndHostController:post_host');


    /*
     * End Host Types
     */
    /* Get all types */
    $app->get('/types[/all]', 'EndHostTypeController:get_type');
    /* Get, update or elete end host type by ID */
    $app->get('/types/id/{end_host_type_id:[0-9]+}[/]', 'EndHostTypeController:get_type_by_id');
    $app->put('/types/id/{end_host_type_id:[0-9]+}[/]', 'EndHostTypeController:update_type');
    $app->delete('/types/id/{end_host_type_id:[0-9]+}[/]', 'EndHostTypeController:delete_type');
    /* Create new end host type */
    $app->post('/types[/new]', 'EndHostTypeController:create_type');


});

/*
 * Reservations
 */
$app->group('/reservations', function () use ($app) {
    /* Get all reservations */
    $app->get('[/{mode:terse}]', 'ReservationController:get_reservations');
    /* Get, update or delete reservation by ID */
    $app->get('/id/{id:[0-9]+}[/{mode:terse}]', 'ReservationController:get_reservation_by_id');
    $app->put('/id/{id:[0-9]+}[/update]', 'ReservationController:put_reservation');
    $app->delete('/id/{id:[0-9]+}[/delete]', 'ReservationController:delete_reservation');
    /* Get specific reservation by IP address */
    $app->get('/ip/{ip:[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}}[/{mode:terse}]',
              'ReservationController:get_reservation_by_ip');
    /* Get all reservations for a MAC address */
    $app->get('/mac/{mac:(?:(?:[0-9A-Fa-f]{4}\.){2}[0-9A-Fa-f]{4}|(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})}[/{mode:terse}]',
              'ReservationController:get_reservation_by_mac');
    /* Get all reservations from specific subnet */
    $app->get('/subnet/{subnet_id:[0-9]+}[/{mode:terse}]', 'ReservationController:get_reservations_for_subnet');
    /* Get all reservations from specific group */
    $app->get('/group/{group_id:[0-9]+}[/{mode:terse}]', 'ReservationController:get_reservations_for_group');
    /* Create new reservation */
    $app->post('[/new]', 'ReservationController:post_reservation');

});

/*
 * Subnets
 */
$app->group('/subnets', function () use ($app) {
    /* Get all subnets */
    $app->get('[/all]', 'SubnetController:get_subnets');
    /* Get, update or delete subnet by ID */
    $app->get('/id/{subnet_id:[0-9]+}', 'SubnetController:get_subnet_by_id');
    $app->put('/id/{subnet_id:[0-9]+}', 'SubnetController:update_subnet');
    $app->delete('/id/{subnet_id:[0-9]+}', 'SubnetController:delete_subnet');
    /* Get free addresses from subnet */
    $app->get('/id/{subnet_id:[0-9]+}/free', 'SubnetController:get_subnet_free_addresses');
    /* Get subnet for specific IP */
    $app->get('/ip/{ip:[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}}', 'SubnetController:get_subnet_by_address');
    /* Get subnet by VLAN ID */
    $app->get('/vlan/{vlan_id:[0-9]+}', 'SubnetController:get_subnet_by_vlan');
    /* Create new subnet */
    $app->post('[/new]', 'SubnetController:create_subnet');

    /*
     * Subnet groups
     */
    $app->group('/id/{subnet_id:[0-9]+}/groups', function () use ($app) {
        $app->get('[/all]', 'GroupController:get_groups');
        $app->get('/id/{group_id:[0-9]+}', 'GroupController:get_group_by_id');
        $app->put('/id/{group_id:[0-9]+}', 'GroupController:put_group');
        $app->delete('/id/{group_id:[0-9]+}', 'GroupController:delete_group');
        $app->post('[/new]', 'GroupController:post_group');
    });
});