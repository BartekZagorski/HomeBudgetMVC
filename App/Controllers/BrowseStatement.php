<?php

namespace App\Controllers;

use \Core\View;
use App\Models\Income;
use App\Models\Expense;

class BrowseStatement extends Authenticated
{
    public function showAction()
    {
        View::renderTemplate('BrowseStatement/show.html');
    }

    public function renderStatementAction()
    {
        if (isset($_POST['beginDate'])) $beginDate = date("Y-m-d", strtotime($_POST['beginDate']));
        else $beginDate = date("Y-m-d", strtotime("first day of this month"));
        if (isset($_POST['endDate'])) $endDate = date("Y-m-d", strtotime($_POST['endDate']));
        else $endDate = date("Y-m-d", strtotime("last day of this month"));
        $header = $_POST['header'] ?? $header = "Przegląd bilansu z bieżącego miesiąca ";

        $incomes = Income::getIncomesOfCurrentUser($_SESSION['user_id'], $beginDate, $endDate);
        $incomesAccordingToCattegories = Income::getSumsOfIncomesAccordingToCattegories($_SESSION['user_id'], $beginDate, $endDate);
        $expenses = Expense::getExpensesOfCurrentUser($_SESSION['user_id'], $beginDate, $endDate);
        $expensesAccordingToCattegories = Expense::getSumsOfExpensesAccordingToCattegories($_SESSION['user_id'], $beginDate, $endDate);

        View::renderTemplate('BrowseStatement/Statement.html',[
            'header' => $header,
            'beginDate' => $beginDate,
            'endDate' => $endDate,            
            'incomes' => $incomes,
            'incomesAccordingToCattegories' => $incomesAccordingToCattegories,
            'expenses' => $expenses,
            'expensesAccordingToCattegories' => $expensesAccordingToCattegories
        ]);
    }
}