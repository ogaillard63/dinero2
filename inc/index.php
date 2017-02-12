<?php
/**
 * @project		WebApp Generator
 * @author		Olivier Gaillard
 * @version		1.0 du 04/06/2012
 * @desc	   	Accueil
 */

// http://croisoft.com/projects/adminman/pages-login.html

require_once( "inc/prepend.php" );
$user->isLoggedIn(); // Espace privé
$user->isAllowed(SUPER_ADMIN);

$smarty->assign("page_title", "Dashboard");

$account_manager = new AccountManager($bdd);
$transaction_manager = new TransactionManager($bdd);
$accounts_list = $account_manager->getActiveAccounts();
$smarty->assign("accounts", $accounts_list);
$smarty->assign("monthly_income", $transaction_manager->getMonthlyIncome(10));
$smarty->assign("monthly_expense", $transaction_manager->getMonthlyExpense(10));
$smarty->assign("uncategorized_transactions", $transaction_manager->getNbUncategorizedTransactions());
$smarty->assign("unreconciled_transactions", $transaction_manager->getNbUnreconciledTransactions());
$smarty->assign("titre", "Homepage");

// get data for balance graph from a year ago until now
$dataset = array();
$yearAgo = strtotime("-1 year", time());
$solde = 0;
for ($an=date('Y', $yearAgo); $an<=date("Y"); $an++) {
	for ($mois=1; $mois<=12; $mois++) {
		$queryDate = sprintf("%4d-%02d-01",$an,$mois);
		if (($queryDate > date('Y-m-d', $yearAgo)) AND ($queryDate <= date("Y-m-d"))) {
			foreach ($accounts_list as $account) {
				$solde += $transaction_manager->getBalanceAtDate($queryDate, $account->id);
				}
			$dataset[] = array(strtotime($queryDate) * 1000, round($solde/1000));
			echo $solde."<br/>";
			}
		$solde = 0;
	}
}

$smarty->assign("dataset", json_encode($dataset));
$smarty->assign("content", "misc/dashboard.tpl.html");
$smarty->display("main.tpl.html");
?>
