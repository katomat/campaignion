<?php

namespace Drupal\campaignion_paypal;

class ECPaymentController extends \PayPalPaymentECPaymentMethodController {
  /**
   * Implements PaymentMethodController::execute().
   */
  function execute(\Payment $payment) {
    // Prepare the PayPal checkout token.
    $authentication = NULL;
    if ($payment->pid) {
      $authentication = $this->loadAuthentication($payment->pid);
    }
    if (!$authentication) {
      entity_save('payment', $payment);
      $authentication = $this->setExpressCheckout($payment);
      if ($authentication) {
        $this->saveAuthentication($authentication);
      }
    }

    // Start checkout.
    if ($authentication) {
      $context = $payment->context_data['context'];
      $context->redirect($this->checkoutURL($payment->method->controller_data['server'], $authentication->token));
    }
    else {
      $payment->setStatus(new PaymentStatusItem(PAYMENT_STATUS_FAILED));
    }
  }
}
