# Dotkernel API Documentation

You can access Dotkernel API documentation by importing the provided collection and environment files into Postman.

## Requirements

* [Postman](https://www.postman.com/downloads/)
* [Bruno](https://www.usebruno.com/)

## Setup

At this point, we assume you already have an API client installed (Postman or Bruno).
The following steps will be performed in the API client.

### Import project files

For Postman:

* click **File** -> **Import** -> **Upload Files**
* navigate to the [documentation](/documentation) directory
* select both [Dotkernel_API.postman_collection.json](/documentation/Dotkernel_API.postman_collection.json) and [Dotkernel_API.postman_environment.json](/documentation/Dotkernel_API.postman_environment.json)
* click **Import**

For Bruno:

- Open the `My Workspace` dropdown and select `Import workspace`.
- Either click-and-drag the `Dotkernel_API.zip` over the form or navigate to it via the `choose a file` link.
- Click the `Import` button.

You should see a new collection (`Dotkernel_API`) added to your collection list, containing the documentation of all Dotkernel API endpoints.

Also, you should see a new environment (`Dotkernel_API`) added to your environments.
This contains a variable, called `APPLICATION_URL` set to `http://api.dotkernel.localhost` by default.
If your application runs on a different URL/port (virtualhost), modify this variable accordingly.

## Usage

Dotkernel API Endpoints are secured with OAuth2.
Calling an endpoint must include an access token sent via the `Authorization` header (see the `Authorization` or `Auth` tab in the collection).

### Add a new request (endpoint)

* Right-click on the parent directory you want to create the request inside, then click **Add Request**.
* Enter the request name and description.
* Select the proper request method:
    * **DELETE**: if you are deleting an item.
    * **GET**: if you are viewing an item or a list of items.
    * **PATCH**: if you are (partially) updating an item.
    * **PUT**: depending on if it exists or not, update or create an item. 
    * **POST**: if you are creating an item.
* If needed, add query parameters (`Params` tab).
* Enter request URL (eg: `{{APPLICATION_URL}}/example`): you can use the existing `APPLICATION_URL` environment variable by placing it between double curly braces.
* Select body (`Body` tab) format based on the data your endpoint expects:
    * Use **none** if no data will be sent to this endpoint.
    * Use **form-data** if besides form data, this endpoint accepts file attachments as well.
    * Use **raw** (also, set Content-Type to **JSON**) for creating/updating items.

The `Authorization` header will be included automatically for new requests (under `Authorization` tab: `Type` is set to `Inherit auth from parent`).
If your request needs to be public (accessible by guest users), you need to set `Type` to `No Auth`.
