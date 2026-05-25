# Dotkernel API Documentation

You can access Dotkernel API documentation by importing the provided collection and environment files into Postman.

## Requirements

- [Postman](https://www.postman.com/downloads/)
- [Bruno](https://www.usebruno.com/)

## Setup

At this point, we assume you already have an API client installed (Postman or Bruno).
The following steps will be performed in the API client.

### Import the Collection and Environment

#### For Postman

- Click **File** -> **Import** -> **Upload Files**.
- Navigate to the [documentation](/documentation) directory.
- Select **both** [Dotkernel_API.postman_collection.json](/documentation/Dotkernel_API.postman_collection.json) and [Dotkernel_API.postman_environment.json](/documentation/Dotkernel_API.postman_environment.json).
- Click **Import**.

#### For Bruno using the Bruno Export Zip

- Open the `My Workspace` dropdown and select `Import workspace`.
- Either click-and-drag the [Dotkernel_API_Bruno.zip](/documentation/Dotkernel_API_Bruno.zip) file over the form or navigate to it via the `choose a file` link.
- Click the `Import` button.

#### For Bruno using Postman Files

> Bruno also supports the Postman files we used for the Postman import.
> If you have already imported the collection using the `.zip` file, you can skip this step.

**Alternatively** import the collection by following these steps:

- Click on `+` next to `Collection` and select `Import Collection`.
- Import [Dotkernel_API.postman_collection.json](/documentation/Dotkernel_API.postman_collection.json) to save the endpoints.
- Select the new collection, then click on `0 collection environments`.
- Either click-and-drag the [Dotkernel_API.postman_environment.json](/documentation/Dotkernel_API.postman_environment.json) file over the form or navigate to it via the `Import your environments` link to save it to the collection.

### Collection Contents

If the import process completed successfully, you should have:

- A new collection (`Dotkernel_API`) containing the documentation of all Dotkernel API endpoints.
- A new environment (`Dotkernel_API`) with the Application URL and OAuth2 tokens (empty by default, then overwritten by the API client).

> The environment contains a variable, called `APPLICATION_URL` set to `http://api.dotkernel.localhost` by default.
> If your application runs on a different URL/port (virtualhost), modify this variable accordingly.

## Usage

Dotkernel API Endpoints are secured with OAuth2.
Calling an endpoint must include an access token sent via the `Authorization` header (see the `Authorization` or `Auth` tab in the collection).

### Add a new request (endpoint)

- Right-click on the parent directory you want to create the request inside, then click `Add Request` (or `New Request`).
- Enter the request name and the description, if available.
- Select the request method:
    - `DELETE` if you are deleting an item.
    - `GET` if you are viewing an item or a list of items.
    - `PATCH` if you are (partially) updating an item.
    - `PUT` depending on if it exists or not, update or create an item. 
    - `POST` if you are creating an item.
- Enter request URL (eg: `{{APPLICATION_URL}}/example`): you can use the existing `APPLICATION_URL` environment variable by placing it between double curly braces.
- If needed, add query parameters (`Params` tab).
- Select body (`Body` tab) format based on the data your endpoint expects:
    - Use `none` or `no body` if no data will be sent to this endpoint.
    - Use `form-data` or `Multipart Form` if besides form data, this endpoint accepts file attachments as well.
    - Use `raw`, then `JSON` for creating/updating items in a JSON format.
- Click `Send` to send the request.

The `Authorization` header will be included automatically for new requests (under `Authorization` tab: `Type` is set to `Inherit auth from parent`).
If your request needs to be public (accessible by guest users), you need to set `Type` to `No Auth`.
