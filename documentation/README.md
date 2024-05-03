# DotKernel API Documentation

You can access DotKernel API documentation by importing the provided collection and environment files into Postman.

## Requirements
* [Postman](https://www.postman.com/downloads/)

## Setup
At this point, we assume you already have Postman installed. The following steps will be performed in Postman.

### Import project files
* click **File** -> **Import** -> **Upload Files**
* navigate to [documentation](/documentation) directory
* select both [DotKernel_API.postman_collection.json](/documentation/DotKernel_API.postman_collection.json) and [DotKernel_API.postman_environment.json](/documentation/DotKernel_API.postman_environment.json)
* click **Import**

You should see a new collection (`DotKernel_API`) added to your collection list, containing the documentation of all DotKernel API endpoints.

Also, you should see a new environment (`DotKernel_API`) added to your environments.
This contains a variable, called `APPLICATION_URL` set to `http://0.0.0.0:8080`.
If your application runs on a different URL/port, modify this variable accordingly.

## Usage

DotKernel API Endpoints are secured with OAuth2, this means that calling an endpoint requires an access token being sent via the `Authorization` header (edit collection root directory and look under `Authorization` tab).

### Add a new request
* right-click on the parent directory you want to create the request inside, then click **Add Request**
* enter name and description for your request 
* select the proper request method:
  * **DELETE**: if you are deleting an item
  * **GET**: if you are viewing an item or a list of items
  * **PATCH**: if you are (partially) updating an item
  * **PUT**: depending on if it exists or not, update or create an item 
  * **POST**: if you are creating an item
* if needed, add query parameters (`Params` tab)
* enter request URL (eg: `{{APPLICATION_URL}}/example`): you can use the existing `APPLICATION_URL` environment variable by placing it between double curly braces
* select body (`Body` tab) format based on the data your endpoint expects:
  * use **none** if no data will be sent to this endpoint
  * use **form-data** if besides form data, this endpoint accepts file attachments as well
  * use **raw** (also, set Content-Type to **JSON**) for creating/updating items

New requests added to the collection will not require adding the `Authorization` header because by default it is inherited from parent directories (under `Authorization` tab: `Type` is set to `Inherit auth from parent`).
If your request should be accessible by guest users, you need to set `Type` to `No Auth`.
