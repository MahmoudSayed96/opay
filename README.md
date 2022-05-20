# Opay
Custom drupal module make an integration with OPay payment gateway.



## Configurations
After install module go to `admin/config/system/opay`
- Set `Token`
- Set `Merchant Id`
- Set `API Base Url`

## Usage/Examples

```php
<?php
namespace Drupal\mental_health\Controller;
use Drupal\Core\Controller\ControllerBase;
/**
 * Returns responses for opay routes.
 */
class OPayController extends ControllerBase {

  /**
   * OPay client instance.
   *
   * @var \Drupal\opay\Client\OPayApiClient.
   */
  private $opayClient;

  public function __construct() {
    $this->opayClient = \Drupal::service('opay.client');
  }

  /**
   * Create opay payment action.
   */
  public function pay(array $query = [], array $data = []) {
    $body = [
      "amount" => [
        "currency" => "EGP",
        "total" =>  $data['amount'] * 100,
     ],
    "country" => "EG",
    "product" => [
        "description" => "description",
        "name" => "name"
    ],
    "reference" => time(),
    "payMethod" => "BankCard",
    'returnUrl' => $data['returnUrl'],
    'callbackUrl' => $data['callbackUrl'],
    'cancelUrl' => $data['cancelUrl'],
    ];
    return $this->opayClient->request('POST', 'create',$query, $body);
  }

}
```

## Reference
- [OPay](https://doc.opaycheckout.com/reference-code)

- [End-to-End Test](https://doc.opaycheckout.com/end-to-end-testing)

## Author

- [@MahmoudSayed96](https://www.github.com/MahmoudSayed96)
