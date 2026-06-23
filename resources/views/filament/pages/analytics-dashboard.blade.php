<x-filament-panels::page>
    <x-filament-widgets::widgets
        :columns="2"
        :data="$this->getWidgetData()"
        :widgets="$this->getVisibleWidgets()"
    />
</x-filament-panels::page>
