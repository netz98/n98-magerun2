---
title: routes:api:list
sidebar_label: routes:api:list
---

# `routes:api:list`

Lists all registered API routes and their corresponding modules or handlers in your Magento 2 installation.

This command is helpful for developers to quickly get an overview of all available API endpoints, which is particularly useful when working with integrations or debugging API-related issues. This command is for Magento 2 installations only.

## Usage

```bash
n98-magerun2 routes:api:list
```

## Description

The command operates specifically on Magento 2 installations. It inspects the application's router list, focusing on the `\Magento\Webapi\Controller\Router` to identify and list API routes.

The output includes:
- **Area**: Typically `webapi` for these routes.
- **Route Path**: The URL path for the API endpoint.
- **HTTP Method**: The HTTP method (e.g., GET, POST, PUT, DELETE) associated with the route.
- **Handler/Service**: The controller class and method, or service class and method, that handles the API request.

The information is presented in a table format for easy readability. If no specific API routes are found via the Webapi Router, a message indicating this will be displayed.

## Example Output

```
+--------+-------------------------------------------+-------------+------------------------------------------------------------------------------------------+
| Area   | Route Path                                | HTTP Method | Handler/Service                                                                          |
+--------+-------------------------------------------+-------------+------------------------------------------------------------------------------------------+
| webapi | /V1/carts/mine/payment-information        | POST        | Magento\Checkout\Api\PaymentInformationManagementInterface::savePaymentInformationAndPlaceOrder |
| webapi | /V1/carts/mine/shipping-information       | POST        | Magento\Checkout\Api\ShippingInformationManagementInterface::saveAddressInformation        |
| webapi | /V1/products/:sku                         | GET         | Magento\Catalog\Api\ProductRepositoryInterface::get                                        |
| ...    | ...                                       | ...         | ...                                                                                      |
+--------+-------------------------------------------+-------------+------------------------------------------------------------------------------------------+
```

## Notes

- The command requires a successfully initialized Magento 2 application to function correctly.
- The Magento 2 route detection for API endpoints may use reflection to access necessary route details from the Webapi router.

## Credits

This command is based on the original `dev:module:routes:list` command developed by **bitExpert AG**. Special thanks to them for their contribution to the Magento open-source community.
The original code can be found at [bitExpert/magerun2-list-all-routes](https://github.com/bitExpert/magerun2-list-all-routes).
