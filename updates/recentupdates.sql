ALTER TABLE FILES$DEBTORS ADD CREDIT_LIMIT DOM_MONEY#
create index ix_debtor_credit_limit on FILES$DEBTORS computed by (CREDIT_LIMIT)#
