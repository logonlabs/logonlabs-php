<?php
namespace LogonLabs;


class ForceAuthenticationTypes {
    const Off = 'off';
    const Attempt = 'attempt';
    const Force = 'force';
    public static $forceAuthenticationTypes = array(self::Off, self::Attempt, self::Force);
}