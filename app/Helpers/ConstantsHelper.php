<?php
define('COMMON_PAGINATION', 6);
define('LIMIT_ROWS', 10);
define('REDIRECT_URL', 'admin.show');

define('ILLEGAL_ACCESS', 'illegal access');
define('SUCCESS_STATUS', 200);
define('NOT_FOUND', 404);
define('SERVER_ERROR', 500);
define('ILLEGAL_ACCESS_CODE', 403);
define('PAYMENT_REQUIRED', 402);
define('VALIDATION_ERROR', 402);
define('SUPER_ADMIN', "1");
define('ORDINARY_ADMIN', "2");
define('LOCATION_LONG', 45.0371918);
define('LOCATION_LAT', 23.8726471);
define('CACHE_TYPE', "1");
define('BANK_TRANSACTION_TYPE', "2");
define('UNSUPPORTED_TYPE', "unsupported types");
define('SINGLE_TYPE', "1");
define('COMPANY_TYPE', "2");
define('DRIVER', "2");
define('CLIENT', "1");
define('ACTIVE', "1");
define('INACTIVE', "2");

/**
 * JQUERY DATE FORMAT
 */
define('DATE_FORMAT', "yyyy-mm-dd");
/**
 *
 * REQUEST'S STATUS
 *
 */
define('FOR_CONFIRMING', "1");
define('CONFIRMED', "2");
define('PAYED', "3");
define('UNDER_PREPARING', "4");
define('PREPARED', "5");
define('DELIVERING', "6");
define('DELIVERED', "7");
define('CANCELED', "8");
define('REFUSED', "9");
define('REQUEST_IDENTIFIER_LENGTH', 10);
define('ADD_THEN_CONTINUE', 2);
define('REQUEST_DONE', 1);
define('NOT_SENT_TO_CONFIRMED', NULL);

/**
 *
 * Api functions
 *
 */
define('SUCCESS', "success");
define('ERROR', "error");


/**
 *
 * OFFER'S STATUS
 *
 */

define('FREE_DELIVERING', "1");
define('PRICE_DISCOUNT', "2");
define('ALL_OFFER', "3");
define('UNSUPPORTED_OFFER_TYPES_CODE', 343);
define('INCORRECT_EXPIRE_DATE', 'incorrect expire date');
define('INCORRECT_EXPIRE_DATE_CODE', 1010);
define('ALL_LIBRARY_BOOKS', "1");
define('NOT_ALL_LIBRARY_BOOKS', "2");


/**
 *
 * ADVERTISEMENT'S CONSTANTS
 *
 */
define('ADS_DIR', "ads");


/**
 *
 * NOTIFICATION'S CONSTANTS
 *
 */
define('CLIENT_NOTIFICATION', "1");
define('DRIVER_NOTIFICATION', "2");
define('LIBRARY_NOTIFICATION', "3");
define('TYPE_ALL', "1");
define('TYPE_NOT_ALL', "2");
define("SYSTEM_NOTIFICATION", "1");
define("FIREBASE_ERROR", "FIREBASE_ERROR");


define('PROMOCODE_LENGTH', 63);
define('DEFAULT_CATEGORY_BOOKS_NUMBER', 4);
define('DEFAULT_CATEGORY_PAGINATION_NUMBER', 6);
define('DEFAULT_BOOK_PAGINATION_NUMBER', 5);
define('DEFAULT_LIBRARY_PAGINATION_NUMBER', 5);







