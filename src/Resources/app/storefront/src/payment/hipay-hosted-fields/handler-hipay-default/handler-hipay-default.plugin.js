import HipayHostedFieldsPlugin from '../hipay-hosted-fields.plugin';

/**
 * Plugin hipay for hosted fields
 */
export default class HandlerHipayDefaultPlugin extends HipayHostedFieldsPlugin {
  static options = {
    username: null,
    password: null,
    environment: null,
    lang: null,
    styles: null,
    idResponse: 'hipay-response'
  };

  getPaymentDefaultOption() {
    return {};
  }

  getPaymentName() {
    return this.options.paymentProductName;
  }

  /**
   * Generate hosted fields configuration
   */
  getConfigHostedFields() {
    const config = {
      template: 'auto',
      selector: 'default-selector'
    };

    if (this.options.styles) {
      config.styles = this.options.styles;
    }

    return config;
  }
}
