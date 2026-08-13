---
title: routes:api:list
sidebar_label: routes:api:list
---

# `routes:api:list`

Lists all registered API routes and their corresponding modules or handlers in your Magento 2 installation.

This command is helpful for developers to quickly get an overview of all available API endpoints, which is particularly useful when working with integrations or debugging API-related issues. This command is for Magento 2 installations only.

## Usage

```bash
n98-magerun2 routes:api:list [options]
```

## Options

- **`-a, --area[=AREA]`**: Filter routes by area (e.g., `webapi`).
- **`-p, --path[=PATH]`**: Filter routes by path pattern (partial/case-insensitive match, e.g., `carts`).
- **`-m, --method[=METHOD]`**: Filter routes by HTTP method (case-insensitive, e.g., `GET`, `POST`, `PUT`, `DELETE`, `PATCH`).
- **`--format[=FORMAT]`**: Output Format. One of [csv,tsv,json,json_array,jsonl,yaml,markdown,xml].

## Description

The command operates specifically on Magento 2 installations. It inspects the application's Web API configuration to identify and list API routes.

On decorated terminals, the HTTP methods/verbs in the output table are colored for better readability (e.g., GET is green, POST is cyan, PUT/PATCH is yellow, DELETE is red) and variable names in the path (e.g., `:itemId`) are highlighted in magenta.

The output includes:
- **area**: Typically `webapi` for these routes.
- **route_path**: The URL path for the API endpoint.
- **method**: The HTTP method associated with the route.
- **handler**: The service class and method that handles the API request.

The information is presented in a table format for easy readability. If no specific API routes are found matching the filters, a message indicating this will be displayed.

## Example Output

```
+--------+-------------------------------------------+-------------+------------------------------------------------------------------------------------------+
| area   | route_path                                | method      | handler                                                                                  |
+--------+-------------------------------------------+-------------+------------------------------------------------------------------------------------------+
| webapi | /V1/carts/mine/payment-information        | POST        | Magento\Checkout\Api\PaymentInformationManagementInterface::savePaymentInformationAndPlaceOrder |
| webapi | /V1/carts/mine/shipping-information       | POST        | Magento\Checkout\Api\ShippingInformationManagementInterface::saveAddressInformation        |
| webapi | /V1/products/:sku                         | GET         | Magento\Catalog\Api\ProductRepositoryInterface::get                                        |
| ...    | ...                                       | ...         | ...                                                                                      |
+--------+-------------------------------------------+-------------+------------------------------------------------------------------------------------------+
```

## Notes

- The command requires a successfully initialized Magento 2 application to function correctly.

## Credits

This command was initially provided by [bitExpert](https://www.bitexpert.de). Special thanks to them for their contribution to the Magento open-source community.
The original code can be found at [bitExpert/magerun2-list-all-routes](https://github.com/bitExpert/magerun2-list-all-routes).
