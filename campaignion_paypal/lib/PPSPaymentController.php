<?php

namespace Drupal\campaignion_paypal;

class PPSPaymentController extends \PayPalPaymentPPSPaymentMethodController {
  public function execute(\Payment $payment) {
    $_SESSION['paypal_payment_pps_pid'] = $payment->pid;
    $payment->context_data['context']->redirect('paypal_payment_pps/redirect/' . $payment->pid);
  }
}
