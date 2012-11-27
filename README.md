oxid-plugin
===============

OXID eSales module for integration with [Tiramizoo API](http://dev.tiramizoo.com/).
Module works with following OXID eSales versions: 4.3.2+, versions 4.4.x, 4.5.x will be available soon

# Installation #

*	Switch to 4.3.2 branch, download code

*   Copy all files from *copy_this* folder to OXID eSales installation path. This step does not overwrite any files.

*   Add these 2 lines to Textarea Shop Modules in **Master Settings -> Core Settings -> System Tab -> Modules**

    ```
    payment => oxtiramizoo_payment
    order => oxtiramizoo_order
    ```

*   Change templates in *changed_full* folder. If you use "basic" template and these files had never been changed, You can overwrite them.

    **file: out/basic/tpl/email_order_cust_html.tpl**

    ```
    @@ -354,7 +354,14 @@
         [{ $order->oxorder__oxdelcountry->value }]<br>
       [{/if}]

    -  [{if $payment->oxuserpayments__oxpaymentsid->value != "oxempty"}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGCARRIER" }] <strong>[{ $order->oDelSet->oxdeliveryset__oxtitle->value }]</strong><br>[{/if}]
    +  [{if $payment->oxuserpayments__oxpaymentsid->value != "oxempty"}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGCARRIER" }] <strong>[{ $order->oDelSet->oxdeliveryset__oxtitle->value }]</strong>
    +  <br>
    +
    +  [{if $order->oxorder__tiramizoo_tracking_url->value }]
    +    Tracking URL: [{$order->oxorder__tiramizoo_tracking_url->value}]<br>
    +  [{/if}]
    +
    +  [{/if}]

       [{if $payment->oxuserpayments__oxpaymentsid->value == "oxidpayadvance"}]
         [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_BANK" }] [{$shop->oxshops__oxbankname->value}]<br>

    ```

    **file: out/basic/tpl/email_order_cust_plain.tpl**

    ```
    @@ -119,6 +119,9 @@

     [{if $payment->oxuserpayments__oxpaymentsid->value != "oxempty"}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGCARRIER" }] [{ $order->oDelSet->oxdeliveryset__oxtitle->getRawValue() }]
     [{/if}]
    +[{if $order->oxorder__tiramizoo_tracking_url->value }]
    +  Tracking URL: [{$order->oxorder__tiramizoo_tracking_url->value}]
    +[{/if}]

     [{if $payment->oxuserpayments__oxpaymentsid->value == "oxidpayadvance"}]
     [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_BANK" }] [{$shop->oxshops__oxbankname->getRawValue()}]<br>

    ```

    **file: out/basic/tpl/payment.tpl**

    ```
    @@ -34,9 +34,33 @@
                     [{ /if}]
                   </div>
               </div>
    +
    +          [{if $isTiramizooPaymentView}]
    +            [{if $oView->isTiramizooCurrentShiippingMethod()}]
    +            <br />
    +            <br />
    +            <h3>[{ oxmultilang ident="oxTiramizoo_selectTimeWindowTitle" }]</h3>
    +
    +                <dl style="margin-top:16px;">
    +                [{foreach key=sDeliveryTime from=$oView->getAvailableDeliveryHours() item=sDeliveryWindow}]
    +                    <dt>
    +                        <input class="selectTiramizooTimeWindow" type="radio" name="sTiramizooTimeWindow" value="[{$sDeliveryTime}]" [{if $oView->getTiramizooTimeWindow() == $sDeliveryTime}]checked="checked"[{/if}] onchange="JavaScript:document.forms.shipping.submit();" />
    +                        <label for="sTiramizooTimeWindow"><b>[{$sDeliveryWindow}]</b></label>
    +                    </dt>
    +                [{/foreach}]
    +                </dl>
    +            [{/if}]
    +          [{/if}]
             </form>
         </div>
       [{/if}]
     
       [{assign var="iPayError" value=$oView->getPaymentError() }]


    ```

    **file: out/basic/tpl/order.tpl**


    ```
    @@ -12,6 +12,13 @@
         <div class="errorbox">[{ oxmultilang ident="ORDER_READANDCONFIRMTERMS" }]</div>
     [{/if}]
     
    +[{ if $isTiramizooOrderView }]
    +  [{ if $oView->isTiramizooError() }]
    +      <div class="errorbox">[{$oView->getTiramizooError()}]</div>
    +  [{/if}]
    +[{/if}]
    +
    +
     [{ if !$oxcmp_basket->getProductsCount()  }]
       <div class="msg">[{ oxmultilang ident="ORDER_BASKETEMPTY" }]</div>
     [{else}]
    @@ -475,7 +482,13 @@
                       <input type="hidden" name="cl" value="payment">
                       <input type="hidden" name="fnc" value="">
                       [{assign var="oShipSet" value=$oView->getShipSet() }]
    -                  [{ $oShipSet->oxdeliveryset__oxtitle->value }]&nbsp;<span class="btn"><input id="test_orderChangeShipping" type="submit" value="[{ oxmultilang ident="ORDER_MODIFY3" }]" class="btn"></span>
    +                  [{ $oShipSet->oxdeliveryset__oxtitle->value }]&nbsp;
    +
    +                  [{ if $isTiramizooOrderView }]
    +                    [{ $oView->getTiramizooTimeWindow()}]
    +                  [{/if}]
    +
    +                  <span class="btn"><input id="test_orderChangeShipping" type="submit" value="[{ oxmultilang ident="ORDER_MODIFY3" }]" class="btn"></span>
                   </div>
                 </form>
             </dd>

    ```



*   Configure the module
    -   At the **Tiramizoo -> Settings**

        Tiramizoo URL - Production version [https://api.tiramizoo.com/v1](https://api.tiramizoo.com/v1), testing version [https://sandbox.tiramizoo.com/api/v1](https://sandbox.tiramizoo.com/api/v1)

        Tiramizoo API token - Can be obtained via your user profile, Production version [https://www.tiramizoo.com/](https://www.tiramizoo.com/), testing version [https://sandbox.tiramizoo.com/](https://sandbox.tiramizoo.com/)

    -   At the **Administer Products -> Categories -> Category selection -> Tiramizoo tab**

        You can dynamically assign stanard dimensions for all products inside selected category

    -   At the **Administer Products -> Products -> Article selection -> Tiramizoo tab**

        You can enable or disable Tiramizoo delivery for selected product

# Checking the Tiramizoo delivery status #

Go to *Order tab* to check the current delivery status