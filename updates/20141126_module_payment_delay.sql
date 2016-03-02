create table DEBTORS$PAYMENT_DELAY (
DEBTOR_ID DOM_RECORD_ID,
PAYMENT_DELAY smallint,
CREATION_USER DOM_CURRENT_USER,
CREATION_DATE DOM_DATE
)#
create index ix_debtor_id_payment_delay on DEBTORS$PAYMENT_DELAY computed by (DEBTOR_ID)#
create index ix_number_payment_delay on DEBTORS$PAYMENT_DELAY computed by (PAYMENT_DELAY)#