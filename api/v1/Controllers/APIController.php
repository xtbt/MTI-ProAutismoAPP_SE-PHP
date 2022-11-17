<?php
    class APIController {
        /**
         * Return API output.
         * @return array
         */
        protected function debugResponse($result) {
            $response['statusCodeHeader'] = 'HTTP/1.1 418 Im a Teapot';
            $response['httpResponseCode'] = 418;
            $response['body'] = $result;
            return $response;
        }

        /**
         * Return API output.
         * @return array
         */
        protected function unauthorizedResponse($result) {
            $response['statusCodeHeader'] = 'HTTP/1.1 401 Unauthorized';
            $response['httpResponseCode'] = 401;
            $response['body'] = $result;
            return $response;
        }

        /**
         * Return API output.
         * @return array
         */
        protected function okResponse($result) {
            $response['statusCodeHeader'] = 'HTTP/1.1 200 OK';
            $response['httpResponseCode'] = 200;
            $response['body'] = $result;
            return $response;
        }

        /**
         * Return API output.
         * @return array
         */
        protected function noContentResponse() {
            $response['statusCodeHeader'] = 'HTTP/1.1 204 No Content';
            $response['httpResponseCode'] = 204;
            $response['body'] = ['msj' => 'OPTIONS bypass'];
            return $response;
        }
        
        /**
         * Return API output.
         * @return array
         */
        protected function notFoundResponse($result) {
            $response['statusCodeHeader'] = 'HTTP/1.1 404 Not Found';
            $response['httpResponseCode'] = 404;
            $response['body'] = $result;
            return $response;
        }

        /**
         * Return API output.
         * @return array
         */
        protected function methodNotAllowedResponse() {
            $response['statusCodeHeader'] = 'HTTP/1.1 405 Method Not Allowed';
            $response['httpResponseCode'] = 405;
            $response['body'] = ['error' => 'Method Not Allowed'];
            return $response;
        }

        /**
         * When there is missing data or incorrect data structure from Client to Server.
         * @param string $cause
         * @return array
         */
        protected function notAcceptableResponse ($cause) {
            $response['statusCodeHeader'] = 'HTTP/1.1 406 Not Acceptable';
            $response['httpResponseCode'] = 406;
            $response['body'] = ['error' => $cause];
            return $response;
        }

        /**
         * When there is a validation fail on Server side, for one or many fields.
         * @param array $result
         * @return array
         */
        protected function unprocessableEntityResponse ($result) {
            $response['statusCodeHeader'] = 'HTTP/1.1 422 Unprocessable Entity';
            $response['httpResponseCode'] = 422;
            $response['body'] = $result;
            return $response;
        }
    }
?>