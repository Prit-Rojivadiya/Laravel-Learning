<template>
  <KChart
    :kChartTitle="'Total Cost'"
    :kSeriesType="seriesType"
    :kSeries="ksTotalsMonthly"
    :kCategoryAxis="ksTotalsMonthlyCategoryAxis"
    :kValueAxis="ksTotalsMonthlyValueAxis"
    :kLegendVisible="false"
  />
</template>

<script>

import KChart from '~/components/kendo/chart/Chart'

export default {
  name: "ExpensesTotalByMonthLine",
  props: ['kSeriesType','gridData'],
  components: {
    KChart,
  },
  data: () => ({
    loading: true,
    seriesType: 'column',
    ksTotalsMonthly: null,
    ksTotalsMonthlyValueAxis: null,
    ksTotalsMonthlyCategoryAxis: null,
  }),
  async created () {
    this.loading = false
    if(this.kSeriesType) {
      this.seriesType = this.kSeriesType
    }
    await this.updateChartData()
  },
  methods: {
    async updateChartData(options) {
      this.loading = true
      let chartSeriesTotalCost = []
      let chartSeriesTotalCostCategories = []
      for (let key in this.gridData) {
        chartSeriesTotalCost.push (this.gridData[key]['total_cost'])
        chartSeriesTotalCostCategories.push (this.gridData[key]['month'])
      }
      this.ksTotalsMonthly = [
        {name: "Total", data: chartSeriesTotalCost}
      ]
      this.ksTotalsMonthlyCategoryAxis = {
        categories: chartSeriesTotalCostCategories,
        majorGridLines: {visible: false},
        labels: {rotation: "auto"}
      }
      this.ksTotalsMonthlyValueAxis = {
        line: {visible: false},
        minorGridLines: {visible: true},
        labels: {rotation: "auto", format: "{0:c}"},
      }

      this.loading = false
    },
    refresh () {
      this.updateChartData()
    },
  }
}
</script>

<style scoped>

</style>
