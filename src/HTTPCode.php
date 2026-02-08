<?php

namespace Tivins\FAPI;

enum HTTPCode: int
{
    case OK = 200;
    case Created = 201;
    case Accepted = 202;
    case NoContent = 204;
    case NotFound = 205;
    case Unauthorized = 401;
    case Forbidden = 403;
    case InternalServerError = 500;
    case Unprocessable = 503;
}