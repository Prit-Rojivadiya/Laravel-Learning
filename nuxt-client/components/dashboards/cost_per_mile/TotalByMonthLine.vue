<template>
  <KChart
    :kChartTitle="'CPM'"
    :kSeriesType="seriesType"
    :kSeries="ksCPMMonthly"
    :kCategoryAxis="ksTotalsMonthlyCategoryAxis"
    :kValueAxis="ksTotalsMonthlyValueAxis"
    :kLegendVisible="true"
  />
</template>

<script>

import KChart from '~/components/kendo/chart/Chart'

export default {
  name: "CPMTotalByMonthLine",
  props: ['kSeriesType','gridData'],
  components: {
    KChart,
  },
  data: () => ({
    loading: true,
    seriesType: 'column',
    ksCPMMonthly: null,
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
      let chartSeriesCPMwF = []
      let chartSeriesCPMwoF = []
      let chartSeriesTotalCostCategories = []
      for (let key in this.gridData) {
        chartSeriesCPMwF.push (this.gridData[key]['cpm_fuel'])
        chartSeriesCPMwoF.push (this.gridData[key]['cpm_wo_fuel'])
        chartSeriesTotalCostCategories.push (this.gridData[key]['month'])
      }
      this.ksCPMMonthly = [
        {name: "CPM w/ Fuel", data: chartSeriesCPMwF},
        {name: "CPM w/o Fuel", data: chartSeriesCPMwoF}
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
