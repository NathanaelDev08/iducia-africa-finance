-- Générer les bulletins (payslips) pour tous les employés actifs
INSERT INTO payslips
(company_id, pay_run_id, employee_id, status, period_start, period_end, base_salary, created_at, updated_at)
SELECT
    ec.company_id,
    1,  -- pay_run_id
    ec.employee_id,
    'calculated',
    (SELECT period_start FROM pay_runs WHERE id = 1),
    (SELECT period_end FROM pay_runs WHERE id = 1),
    ec.base_salary,
    NOW(),
    NOW()
FROM employee_contracts ec
WHERE ec.company_id = 1
  AND ec.status = 'active'
  AND ec.start_date <= (SELECT period_end FROM pay_runs WHERE id = 1)
  AND (ec.end_date IS NULL OR ec.end_date >= (SELECT period_start FROM pay_runs WHERE id = 1));

-- Vérifier les résultats
SELECT COUNT(*) as "Bulletins créés" FROM payslips WHERE pay_run_id = 1;
SELECT ps.id, e.first_name, e.last_name, ps.base_salary
FROM payslips ps
JOIN employees e ON ps.employee_id = e.id
WHERE ps.pay_run_id = 1
ORDER BY ps.id;
