<?php
/**
 * @see https://github.com/Shivella/laravel-bitly
 *
 * (c) Wessel Strengholt <wessel.strengholt@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Access Token
    |--------------------------------------------------------------------------
    |
    | Enter here your access token generated from: https://bitly.com/a/oauth_apps
    */

    'accesstoken' => env('BITLY_ACCESS_TOKEN', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Custom Domain
    |--------------------------------------------------------------------------
    |
    | set this if used. Allows for the bitly custom domain usage.
    */
    'custom_domain' => env('BITLY_CUSTOM_DOMAIN', null),
];
