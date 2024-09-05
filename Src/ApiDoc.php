<?php

namespace ApiVacations;

class ApiDoc
{
  public function show()
  {
    echo <<<HEREDOC
    =====================================================================
    
                              API DOCS Vacations
    
    =====================================================================
    
    1. Authorization
    
    
      Title: Create new access token
      
      Request:
        Method: POST
        URI: /auth
        body: {
          login: 'string' //required
          pass: 'string' //required
        }
    
      Response: 
        Success
        {
          "code": 201,
          "response": {
            "groupId": int,
            "id": int,
            "isActive": bool,
            "isAdmin": bool,
            "login": "string",
            "token": "string",
            "validAt": "datetime" //Format: Y-m-d H:i:s
          },
          "status": "OK"
        }
    
        Error
          401, 405, 500 
    
    
      Title: Get simple information about token's owner
    
      Request:
        Method: GET
        URI: /auth
        header: "Authorization: Bearer {USER_OR_ADMIN_TOKEN}"
      
      Response:
        Success
        {
          "code": 200,
          "response": {
            "groupId": int,
            "id": int,
            "isActive": bool,
            "isAdmin": bool,
            "login": "string"
          },
          "status": "OK"
        }
    
        Error
          401, 405, 500 
      
    
      Title: Refresh access token
    
      Request:
        Method: PATCH
        URI: /auth
        header: "Authorization: Bearer {USER_OR_ADMIN_TOKEN}"
    
      Response:
        Success
        {
          "code": 200,
          "response": {
              "token": "string",
              "validAt": "datetime"
          },
          "status": "OK"
        }
    
        Error
          401, 405, 500 
    
    
    =====================================================================
    
    2. Users
    
    
      Title: Create new user 
    
      Request:
        Method: POST
        URI: /users
        body for create workers:
        {
          "login":"string", //required
          "pass":"string", //required
          "isAdmin":false, //required
          "userData":
            {
              "firstName":"string", //required
              "lastName":"string", //required
              "address":"string", //required
              "postalCode":"string", //optional
              "city":"string", //required
              "phone":"string", //required
              "email":"string" //required
            }, 
            "groupId":int //required
        }
    
        body for create owners:
        {
          "login":"string", //required
          "pass":"string", //required
          "isAdmin":true, //required
          "userData":
            {
              "firstName":"string", //required
              "lastName":"string", //required
              "address":"string", //required
              "postalCode":"string",//optional
              "city":"string", //required
              "phone":"string", //required
              "email":"string" //required
            }, 
          "group": 
            {
              "name":"string", //required
              "address":"string", //required
              "postalCode":"string", //optional
              "city":"string", //required
              "nip":"string" //required
            }
        }
    
      Response:
        Success
        {
          "code": 201,
          "response": {
            "createdAt": "datetime",
            "id": int,
            "isActive": bool,
            "isAdmin": bool,
            "login": "string",
            "updatedAt": "datetime",
            "userData": {
              "address": "string",
              "city": "string",
              "createdAt": "datetime",
              "email": "string",
              "firstName": "string",
              "lastName": "string",
              "phone": "string",
              "postalCode": "string",
              "updatedAt": "datetime"
            }
          },
          "status": "OK"
        }
    
        Error
          405, 422, 500 
    
    
    Title: Get users
      Request:
        Method: GET
        URI: /users
        header: "Authorization: Bearer {ADMIN_TOKEN}"
        queryparams: {
          "offset": int, //optional
          "limit": int //optional
        }
    
      Response:
        Success
        {
          "allRows": int,
          "code": 200,
          "response": [
            {
              "createdAt": "datetime",
              "fullName": "string",
              "id": int,
              "isActive": bool,
              "isAdmin": bool,
              "login": "string"
            }
          ],
          "status": "OK"
        }
    
        Error
          401, 403, 404, 405, 500 
    
    
    Title: Get full info about particular user
    
      Request:
        Method: GET
        URI: /users/{id}
        header: "Authorization: Bearer {USER_OR_ADMIN_TOKEN}"
    
      Response:
        Success
        {
          "code": 200,
          "response": {
            "createdAt": "datetime",
            "id": int,
            "isActive": bool,
            "isAdmin": bool,
            "login": "string",
            "updatedAt": "datetime",
            "userData": {
              "address": "string",
              "city": "string",
              "createdAt": "datetime",
              "email": "string",
              "firstName": "string",
              "lastName": "string",
              "phone": "string",
              "postalCode": "string",
              "updatedAt": "datetime"
            }
          },
          "status": "OK"
        }
    
        Error
          401, 403, 404, 405, 500 
        
    
    Title: Edit user data
    
      Request:
        Method: PATCH
        URI: /users/{id}
        header: "Authorization: Bearer {USER_OR_ADMIN_TOKEN}"
        body: {
          {
            "firstName":"string", //optional
            "lastName":"string", //optional
            "address":"string", //optional
            "postalCode":"string", //optional
            "city":"string", //optional
            "isActive":bool //Work only with {ADMIN_TOKEN} //optional
          }
        }
    
      Response:
        Success
        {
          "code": 200,
          "response": {
            "createdAt": "datetime",
            "id": int,
            "isActive": bool,
            "isAdmin": bool,
            "login": "string",
            "updatedAt": "datetime",
            "userData": {
              "address": "string",
              "city": "string",
              "createdAt": "datetime",
              "email": "string",
              "firstName": "string",
              "lastName": "string",
              "phone": "string",
              "postalCode": "string",
              "updatedAt": "datetime"
            }
          },
          "status": "OK"
        }
    
        Error
          401, 403, 404, 405, 422, 500     
    
    
    Title: Delete user
    
      Request:
        Method: DELETE
        URI: /users/{id}
        header: "Authorization: Bearer {ADMIN_TOKEN}"
    
      Response:
        Success
        204 No Content
    
        Error
          401, 403, 404, 405, 500      
    
    
    =====================================================================
    
    3. Groups
    
    
    Title: Get simple information about all groups
    
      Request:
        Method: GET
        URI: /groups
    
      Response:
        Success
        {
          "code": 200,
          "response": [
            {
              "id": int,
              "name": "string",
              "nip": "string",
              "city": "string",
            }
          ],
          "status": "OK"
        }
    
        Error
          405, 500
    
    
    Title: Get full information about particulary group
    
      Request:
        Method: GET
        URI: /groups
        header: "Authorization: Bearer {USER_OR_ADMIN_TOKEN}"
    
      Response:
        Success
        {
          "code": 200,
          "response": {
            "address": "string",
            "city": "string",
            "createdAt": "datetime",
            "id": int,
            "name": "string",
            "nip": "string",
            "postalCode": "string",
            "updatedAt": "datetime",
            "userId": int //id owner's group
          },
          "status": "OK"
        }
    
        Error
          401, 404, 405, 500
    
    
    =====================================================================
    
    4. Events
    
    Title: Get information about all events
    
      Request:
        Method: GET
        URI: /events
        header: "Authorization: Bearer {USER_OR_ADMIN_TOKEN}"
        queryparams: {
          "offset": int, //optional
          "limit": int, //optional
          "userid": int // {USER_TOKEN} - ignored, {ADMIN_TOKEN} - only users belong to his group //optional
        }
    
      Response:
        Success
        {
          "allRows": int,
          "code": 200,
          "response": [
            {
              "createdAt": "datetime",
              "dateFrom": "date", // Y-m-d
              "dateTo": "date",
              "days": int,
              "groupId": int,
              "id": int,
              "notice": "string", // visible only for {USER_TOKEN}
              "reasonId": int,
              "reasonName": "string",
              "status": "enum", // ["approved", "cancelled", "pending"]
              "updatedAt": "datetime",
              "userId": int,
              "wantCancel": "enum" // ["no", "yes"]
            },
          ],
          "status": "OK"
        }
    
        Error
          401, 404, 405, 500
    
    
    Title: Get information about particular event
    
      Request:
        Method: GET
        URI: /events/{id}
        header: "Authorization: Bearer {USER_OR_ADMIN_TOKEN}"
    
      Response:
        Success
        {
          "code": 200,
            "response": {
              "createdAt": "datetime",
              "dateFrom": "date",
              "dateTo": "date",
              "days": int,
              "groupId": int,
              "id": int,
              "notice": "string", // visible only for {USER_TOKEN}
              "reasonId": int,
              "reasonName": "string",
              "status": "enum", // ["approved", "cancelled", "pending"]
              "updatedAt": "datetime",
              "userId": int,
              "wantCancel": "enum" // ["no", "yes"]
            },
          "status": "OK"
        }
    
        Error
          401, 404, 405, 500
    
    
    Title: Create event
    
      Request:
        Method: POST
        URI: /events
        header: "Authorization: Bearer {USER_TOKEN}"
        body: {
          "reasonId": int, //required
          "dateFrom": "datetime", //required
          "dateTo": "datetime", //required
          "notice": "string" //optional
        }
    
      Response:
        Success
        {
          "code": 201,
            "response": {
              "createdAt": "datetime",
              "dateFrom": "date",
              "dateTo": "date",
              "days": int,
              "groupId": int,
              "id": int,
              "notice": "string",
              "reasonId": int,
              "reasonName": "string",
              "status": "enum", // ["approved", "cancelled", "pending"]
              "updatedAt": "datetime",
              "userId": int,
              "wantCancel": "enum" // ["no", "yes"]
            },
          "status": "OK"
        }
    
        Error
          401, 403, 405, 422, 500
    
    
    Title: Edit event
    
      Request:
        Method: PATCH
        URI: /events/{id}
        header: "Authorization: Bearer {USER_OR_ADMIN_TOKEN}"
    
        for {USER_TOKEN}
        body: {
          "reasonId":int, //optional
          "dateFrom":"datetime", //optional
          "dateTo":"datetime", //optional
          "notice":"string", //optional
          "wantCancel":"enun" // ["no", "yes"] optional
        }
    
        for {ADMIN_TOKEN}
        body: {
          "status":"enum" // ["approved", "cancelled", "pending"] optional
        }
    
      Response
        Success
        {
          "code": 200,
            "response": {
              "createdAt": "datetime",
              "dateFrom": "date",
              "dateTo": "date",
              "days": int,
              "groupId": int,
              "id": int,
              "notice": "string", // visible only for {USER_TOKEN}
              "reasonId": int,
              "reasonName": "string",
              "status": "enum", // ["approved", "cancelled", "pending"]
              "updatedAt": "datetime",
              "userId": int,
              "wantCancel": "enum" // ["no", "yes"]
            },
          "status": "OK"
        }
    
        Error
          401, 404, 405, 422, 500
    
    
    Title: Delete event
    
      Request:
        Method: DELETE
        URI: /events/{id}
        header: "Authorization: Bearer {USER_TOKEN}"
    
      Response:
        Success
        204 No Content
    
        Error
          401, 403, 404, 405, 500
    
    
    =====================================================================
    
    5. Reasons
    
    Title: Get reasons
    
      Request:
        Method: GET
        URI: /reasons
        header: "Authorization: Bearer {USER_OR_ADMIN_TOKEN}"
    
      Response:
        Success
        {
          "code": 200,
          "response": [
            {
              "id": int,
              "name": "string"
            },
          ],
          "status": "OK"
        }
    
        Error
          401, 405, 500
    
    
    =====================================================================
    
    Possible error responses:
    
      {
        "code": 401,
        "message": "Unauthorized",
        "status": "error"
      }
    
      {
        "code": 403,
        "message": "Forbidden",
        "status": "error"
      }
    
      {
        "code": 404,
        "message": "Not Found",
        "status": "error"
      }
    
      {
        "code": 405,
        "message": "Method not allowed",
        "status": "error"
      }
    
      {
        "code": 422,
        "message": "string",
        "status": "error"
      }
    
      {
        "code": 500,
        "message": "Server Error",
        "status": "error"
      }
    HEREDOC;

  }
}

