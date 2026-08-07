<?php

namespace App\Filament\Widgets;

use App\Models\BusinessEnquiry;
use App\Models\ContactEnquiry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnquiryStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $thisMonth = now()->startOfMonth();

        $businessThisMonth = BusinessEnquiry::query()->where('created_at', '>=', $thisMonth)->count();
        $newBusiness = BusinessEnquiry::query()->where('status', 'new')->count();
        $unreadContacts = ContactEnquiry::query()->where('is_read', false)->count();

        return [
            Stat::make('Business Enquiries This Month', $businessThisMonth)
                ->description('Partner enquiries received since '.$thisMonth->format('M j'))
                ->color('success'),
            Stat::make('New Enquiries Awaiting Action', $newBusiness)
                ->description('Status: New')
                ->color('warning'),
            Stat::make('Unread Contact Messages', $unreadContacts)
                ->description('From the Contact Us form')
                ->color($unreadContacts > 0 ? 'danger' : 'success'),
        ];
    }
}
