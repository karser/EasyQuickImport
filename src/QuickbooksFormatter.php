<?php declare(strict_types=1);

namespace App;

use QuickBooks_QBXML_Object_Bill;
use QuickBooks_QBXML_Object_Bill_ExpenseLine;
use QuickBooks_QBXML_Object_Customer;
use QuickBooks_QBXML_Object_Invoice;
use QuickBooks_QBXML_Object_Invoice_InvoiceLine;
use QuickBooks_QBXML_Object_Vendor;

class QuickbooksFormatter
{
    public function formatForOutput(string $string, bool $stopOnError = false): string
    {
        $onError = $stopOnError ? 'stopOnError' : 'continueOnError';

        //translate all non-ASCII characters
        $string = transliterator_transliterate('Any-Latin; Latin-ASCII', $string);

        $return = '<?xml version="1.0" encoding="utf-8"?>
			<?qbxml version="13.0"?>
			<QBXML>
				<QBXMLMsgsRq onError="'.$onError.'">
				' . $string . '
				</QBXMLMsgsRq>
            </QBXML>';

        return $return;
    }

    public function getTestPaymentReceiveAdd(): string
    {
        $payment = new \QuickBooks_QBXML_Object_ReceivePayment();
        $payment->setCustomerFullName('Acme Inc.');
        $payment->setARAccountFullName('Accounts Receivable - USD');

        $payment->setTxnDate('2015-10-10');
        $payment->setRefNumber('1000');
        $payment->setTotalAmount('100.00');
        $payment->set('ExchangeRate', '7');
        $payment->setPaymentMethodFullName('Cash');
        $payment->setMemo('Inv. #9460');
        $payment->setDepositToAccountFullName('Citibank USD');

        $payment->setIsAutoApply(true);

        return $this->formatForOutput($payment->asQBXML(QUICKBOOKS_ADD_RECEIVE_PAYMENT));
    }

    public function getTestBillPaymentCheckAdd(): string
    {
        $payment = new \QuickBooks_QBXML_Object_BillPaymentCheck();
        $payment->setPayeeEntityFullName('Brilliant Business Center');
        $payment->setAPAccountFullName('Accounts Payable');
        $payment->setTxnDate('2015-10-10');
        $payment->setBankAccountFullName('Citibank USD');
        $payment->setRefNumber('1000');
        $payment->setMemo('Inv. #9460');
        $payment->set('ExchangeRate', '1');
//        $payment->set('AppliedToTxnAdd TxnID', '100.00');
        $payment->set('AppliedToTxnAdd PaymentAmount', '100.00');

        return $this->formatForOutput($payment->asQBXML(QUICKBOOKS_ADD_BILLPAYMENTCHECK));
    }

    public function getTestJournalEntryAdd(): string
    {
        $entry = new \QuickBooks_QBXML_Object_JournalEntry();
        $entry->setTxnDate('2018-10-10');
        $entry->setRefNumber('');

        $entry->set('ExchangeRate', '8');
        $entry->set('CurrencyRef FullName', 'US Dollar');

        $creditLine = new \QuickBooks_QBXML_Object_JournalEntry_JournalCreditLine();
        $creditLine->setAccountName('Uncategorized Income');
        $creditLine->setAmount('503.00');
        $creditLine->setMemo('');
        $entry->addCreditLine($creditLine);
        $debitLine = new \QuickBooks_QBXML_Object_JournalEntry_JournalDebitLine();
        $debitLine->setAccountName('Citibank USD');
        $debitLine->setAmount('503.00');
        $debitLine->setMemo('');
        $entry->addDebitLine($debitLine);

        return $this->formatForOutput($entry->asQBXML(QUICKBOOKS_ADD_JOURNALENTRY));
    }

    public function getTestCustomerAdd(): string
    {
        //Generate a QBXML object
        $Customer = new QuickBooks_QBXML_Object_Customer();

        $Customer->setFullName('Acme Inc.');
        $Customer->setFirstName('John');
        $Customer->setLastName('Tulchin');
        $Customer->setCompanyName('Acme Inc.');

        $Customer->setBillAddress('56 Cowles Road', '', '', '', '',
            'Willington', 'CT', '', '06279', 'United States');

        $Customer->set('CurrencyRef FullName', 'US Dollar');

        return $this->formatForOutput($Customer->asQBXML(QUICKBOOKS_ADD_CUSTOMER));
    }

    public function getTestInvoiceAdd(): string
    {
//        new QuickBooks_QBXML_ObjectLine
        $Invoice = new QuickBooks_QBXML_Object_Invoice();
        $Invoice->setCustomerFullName('Acme Inc.');
        $Invoice->setRefNumber('A-123');
        $Invoice->setMemo('This invoice was created using the QuickBooks PHP API!');
        $Invoice->setARAccountName('Accounts Receivable - USD');

        $Invoice->setTxnDate('2018-05-03');

        $InvoiceLine1 = new QuickBooks_QBXML_Object_Invoice_InvoiceLine();
        $InvoiceLine1->setItemName('Consulting');
        $InvoiceLine1->setDesc('Item desc rate');
        $InvoiceLine1->setRate(60.00);
        $InvoiceLine1->setQuantity(3);

// 5 items of type "Item Type 2", for a total amount of $225.00 ($45.00 each)
        $InvoiceLine2 = new QuickBooks_QBXML_Object_Invoice_InvoiceLine();
        $InvoiceLine2->setItemName('Consulting');
        $InvoiceLine2->setDesc('Item desc amount');
        $InvoiceLine2->setAmount(225.00);
        $InvoiceLine2->setQuantity(5);

// Make sure you add those invoice lines on to the invoice
        $Invoice->addInvoiceLine($InvoiceLine1);
        $Invoice->addInvoiceLine($InvoiceLine2);

        return $this->formatForOutput($Invoice->asQBXML(QUICKBOOKS_ADD_INVOICE));
    }

    public function getTestVendorAdd(): string
    {
        $vendor = new QuickBooks_QBXML_Object_Vendor();

        $vendor->setName('Brilliant Business Center');
        $vendor->setCompanyName('Brilliant Business Center');
        $vendor->setVendorAddress('Unit 1104A, 11/F, Kai Tak Commercial Building', '317-319 Des Voeux Rd. Central, H.K');
        $vendor->setVendorTypeRef('Service Providers');
        $vendor->set('TermsRef FullName', 'Due on receipt');
        $vendor->set('CurrencyRef FullName', 'Hong Kong Dollar');

        return $this->formatForOutput($vendor->asQBXML(QUICKBOOKS_ADD_VENDOR));
    }

    public function getTestBillAdd(): string
    {
        $bill = new QuickBooks_QBXML_Object_Bill();
        $bill->setVendorFullname('Brilliant Business Center');
        $bill->setMemo('Bill Memo');
        $bill->set('TermsRef FullName', 'Due on receipt');
        $bill->set('APAccountRef FullName', 'Accounts Payable');
        $bill->setRefNumber('vendor-invoice-id');
        $bill->setTxnDate('2018-05-10');

        $line = new QuickBooks_QBXML_Object_Bill_ExpenseLine();
        $line->setAccountFullName('Rent Expense');
        $line->setAmount(10.00);
        $line->setMemo('Item Memo');
        $bill->addExpenseLine($line);

        return $this->formatForOutput($bill->asQBXML(QUICKBOOKS_ADD_BILL));
    }
}
