<?php

namespace App\Controllers;

use \Core\View;
use App\Models\Income;
use App\Models\Expense;

class BrowseStatement extends Authenticated
{
    public function showStatementOfCurrentMonthAction()
    {
        $beginDate = date("Y-m-01");
        $endDate = date("Y-m-t");

        $incomes = Income::getIncomesOfCurrentUser($_SESSION['user_id'], $beginDate, $endDate);
        $incomesAccordingToCattegories = Income::getSumsOfIncomesAccordingToCattegories($_SESSION['user_id'], $beginDate, $endDate);
        $expenses = Expense::getExpensesOfCurrentUser($_SESSION['user_id'], $beginDate, $endDate);
        $expensesAccordingToCattegories = Expense::getSumsOfExpensesAccordingToCattegories($_SESSION['user_id'], $beginDate, $endDate);
        View::renderTemplate('BrowseStatement/showStatementOfCurrentMonth.html',[
            'beginDate' => date("01-m-Y"),
            'endDate' => date("t-m-Y"),            
            'incomes' => $incomes,
            'incomesAccordingToCattegories' => $incomesAccordingToCattegories,
            'expenses' => $expenses,
            'expensesAccordingToCattegories' => $expensesAccordingToCattegories
        ]);
    }
}