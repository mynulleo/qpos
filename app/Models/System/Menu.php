<?php

/**
 * @Quill Information Technology
 */

namespace App\Models\System;

use App\Helpers\GlobalHelper;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Menu extends Model
{
    use LogsActivity;

    protected $table = 'menus';
    protected $guarded = ['id'];
    protected $logName = "Menu";

    protected $appends = ['is_show_dashboard', 'menu_icon_name'];

    public function getDescriptionForEvent(string $eventName): string
    {
        $guard = GlobalHelper::get_guard();
        $name = Auth::guard($guard)->user()->name ?? '';

        return "{$name} - {$eventName} this";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty(true)
            ->logUnguarded(true)
            ->logOnly(['*'])
            ->useLogName($this->logName);
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class);
    }

    public function childs()
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id')->oldest('sorting');
    }

    public function childMenus()
    {
        return $this->childs()->with('childMenus');
    }

    public static function menus()
    {
        try {
            $permittedMenuArr = App::make('permittedMenuArr');

            return Menu::whereNull('parent_id')
                ->where(function ($q) {
                    $q->where('status', 'active')->orWhereNull('status');
                })
                ->with(['childMenus' => function ($query) use ($permittedMenuArr) {
                    $query->where(function ($q) {
                        $q->where('status', 'active')->orWhereNull('status');
                    })
                    ->whereIn('route_name', $permittedMenuArr)
                    ->orderBy('menu_name'); // Sort child menus alphabetically
                }])
                ->oldest('sorting')
                ->get()
                ->filter(function ($parentMenu) use ($permittedMenuArr) {
                    if ($parentMenu->childMenus->isNotEmpty()) {
                        return true;
                    }
                    if (!empty($parentMenu->route_name) && in_array($parentMenu->route_name, $permittedMenuArr)) {
                        return true;
                    }
                    return false;
                })
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function getMenuList()
    {
        $parent = Menu::with(['childs' => function ($q) {
                $q->where(function ($sub) {
                    $sub->where('status', 'active')->orWhereNull('status');
                });
            }])
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })
            ->oldest('sorting')
            ->get();
        $menus = Menu::recursiveMenuList($parent);

        return $menus;
    }

    public static function recursiveMenuList($datas)
    {
        static $menus = [];
        static $level = 0;
        $level++;
        if (! empty($datas)) {
            foreach ($datas as $desiglist) {
                if (! empty($desiglist['childs'])) {
                    $arr = ['id' => $desiglist['id'], 'name' => str_repeat('|__', $level - 1) . $desiglist['menu_name']];
                    $menus[] = $arr;
                } else {
                    $arr = ['id' => $desiglist['id'], 'name' => str_repeat('|__', $level - 1) . $desiglist['menu_name']];
                    $menus[] = $arr;
                }
                Menu::recursiveMenuList($desiglist['childs']);
            }
        }
        $level--;

        return $menus;
    }

    public function getIsShowDashboardAttribute()
    {
        return $this->show_dasboard == 1 ? 'Yes' : 'No';
    }


    public function getMenuIconNameAttribute()
    {
        return $this->icon . ' ' . $this->menu_name;
    }
}
