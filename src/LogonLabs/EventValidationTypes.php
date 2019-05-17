<?php
namespace LogonLabs;


class EventValidationTypes {
    const Pass = 'Pass';
    const Fail = 'Fail';
    const NotApplicable = 'NotApplicable';
    public static $eventValidationTypes = array(self::Pass, self::Fail, self::NotApplicable);
}