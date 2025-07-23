<script setup lang="ts">
import { LineChart, PieChart } from 'echarts/charts';
import { GridComponent, LegendComponent, TitleComponent, TooltipComponent } from 'echarts/components';
import type { EChartsType } from 'echarts/core';
import { init, registerTheme, use } from 'echarts/core';
import { SVGRenderer } from 'echarts/renderers';
import { colors } from 'tailwind.config';
import { onMounted, onUpdated, ref } from 'vue';

type Props = {
    manuscriptCountByStatus: { value: number, name: string }[];
    authorsVsManuscripts: { months: string[], authors?: number[], manuscripts: number[] };
}

const props = defineProps<Props>()

registerTheme('ijmems', {
    color: [
        colors.primary[500],
        colors.primary[400],
        colors.primary[300],
        colors.primary[200],
        colors.primary[100],
        colors.primary[50],
        colors.primary[600],
        colors.primary[700],
        colors.primary[800],
        colors.primary[900],
        colors.primary[950],
    ],
    textStyle: {
        fontFamily: 'inherit',
        fontSize: 12,
    }
});

use([TitleComponent, TooltipComponent, GridComponent, LegendComponent, LineChart, PieChart, SVGRenderer]);

let pieChart: EChartsType, lineChart: EChartsType;

const [pieChartContainer, lineChartContainer] = [ref<HTMLElement>(), ref<HTMLElement>()];

const initCharts = () => {
    const { manuscriptCountByStatus, authorsVsManuscripts: { months, ...authorsVsManuscripts } } = props;

    pieChart?.setOption({
        title: {
            text: 'Manuscripts',
            left: 'center',
        },
        tooltip: {
            trigger: 'item'
        },
        legend: {
            orient: 'horizontal',
            bottom: 10,
            itemGap: 20
        },
        series: [
            { type: 'pie', radius: '50%', data: manuscriptCountByStatus }
        ],
    }, true);

    const lineChartSeries = Object.keys(authorsVsManuscripts).map(name => ({
        name: titleCase(name),
        data: authorsVsManuscripts[name as keyof typeof authorsVsManuscripts],
        type: 'line',
        smooth: true
    }));

    lineChart?.setOption({
        title: {
            text: lineChartSeries?.map(({ name }) => name).join(' vs '),
            left: 'center',
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            orient: 'vertical',
            bottom: 'bottom',
            itemGap: 50
        },
        yAxis: {
            type: 'value'
        },
        xAxis: {
            type: 'category',
            data: months
        },
        series: lineChartSeries,
    }, true);
}

onMounted(() => {
    pieChart = init(pieChartContainer.value!, 'ijmems');
    lineChart = init(lineChartContainer.value!, 'ijmems');

    initCharts();
});

onUpdated(initCharts)

function titleCase(str: string) {
    return str.toLowerCase().replace(/\b\w/g, s => s.toUpperCase());
}
</script>

<template>
    <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div ref="pieChartContainer" class="h-96 bg-white shadow rounded-lg py-4"></div>
        <div ref="lineChartContainer" class="md:col-span-2 h-96 bg-white shadow rounded-lg py-4"></div>
    </div>
</template>

<style scoped lang="scss"></style>