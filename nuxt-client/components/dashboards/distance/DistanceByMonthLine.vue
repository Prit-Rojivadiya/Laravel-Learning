<template>
  <KChart
    :kChartTitle="'Distance Traveled'"
    :kSeriesType="seriesType"
    :kSeries="ksDistanceMonthly"
    :kCategoryAxis="ksTotalsMonthlyCategoryAxis"
    :kValueAxis="ksTotalsMonthlyValueAxis"
    :kLegendVisible="false"
    :kToolTip="kToolTip"

  />
</template>
<script>

import KChart from '~/components/kendo/chart/Chart'

export default {
  name: "DistanceByMonthLine",
  props: ['kSeriesType','gridData'],
  components: {
    KChart,
  },
  data: () => ({
    loading: true,
    seriesType: 'column',
    ksDistanceMonthly: null,
    ksTotalsMonthlyValueAxis: null,
    ksTotalsMonthlyCategoryAxis: null,
    kToolTip: null,
  }),
  async created () {
    this.loading = false
    if(this.kSeriesType) {
      this.seriesType = this.kSeriesType
    }
    this.kToolTip = {
      visible: true,
      template: "#= kendo.format('{0:n2}',value) #"
    }

    await this.updateChartData()
  },
  methods: {
    async updateChartData(options) {
      this.loading = true
      let chartSeriesTotalDistance = []
      let chartSeriesTotalCostCategories = []
      for (let key in this.gridData) {
        chartSeriesTotalDistance.push (this.gridData[key]['distance'])
        chartSeriesTotalCostCategories.push (this.gridData[key]['month'])
      }
      this.ksDistanceMonthly = [
        {name: "Total", data: chartSeriesTotalDistance}
      ]
      this.ksTotalsMonthlyCategoryAxis = {
        categories: chartSeriesTotalCostCategories,
        majorGridLines: {visible: false},
        labels: {rotation: "auto"}
      }
      this.ksTotalsMonthlyValueAxis = {
        line: {visible: false},
        minorGridLines: {visible: true},
        labels: {rotation: "auto", format: "{0:n}"},
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
