<?php

class DeviceController extends Controller {
    protected $db;
    protected $devicesModel;
    protected $rfDeviceModel;
    protected $userDevicesModel;
    
    function __construct() {
        parent::__construct();
        $db = $this->db;
        $this->devicesModel = new DevicesModel($db);
        $this->rfDeviceModel = new RFDeviceModel($db);
        $this->userDevicesModel = new UserDevicesModel($db);
    }

    function devices($f3, $args) {
        $currentUser = $this->currentUser($f3);
        $devicesForCurrentUser = $this->devicesForUser($currentUser->ID);
        $f3->set("name", $currentUser->Name);
        $f3->set("devices", $devicesForCurrentUser);
        $template = new Template;
        echo $template->render("devices.html");
    }

    function add($f3) {
        $currentUserId = $this->currentUser($f3)->ID;
        $this->devicesModel = new DevicesModel($this->db);
        $deviceId = $this->devicesModel->add();
        $this->rfDeviceModel->add($deviceId);
        $this->userDevicesModel->add($currentUserId, $deviceId);
        $f3->reroute("@devices");
    }

    function delete($f3, $args) {
        $deviceId = $args["id"];
        $this->userDevicesModel->delete($deviceId);
        $this->rfDeviceModel->delete($deviceId);
        $this->devicesModel->delete($deviceId);
        $f3->reroute("@devices");
    }

    private function devicesForUser($userId) {
        $userDevicesView = new DB\SQL\Mapper($this->db, "UserDevicesView");
        $devicesForUser = $userDevicesView->find(array("UserDevices_UserID = ?", $userId));

        return $devicesForUser;
    }

    private function currentUser($f3) {
        $userModel = new UserModel($this->db);
        $currentUser = $userModel->findUser($f3->get("SESSION.user"))[0];

        return $currentUser;
    }
}