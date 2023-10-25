<template>
  <KChart
    :kChartTitle="'Total Cost'"
    :kSeriesType="'pie'"
    :kSeries="ksTotalsPie"
    :kCategoryAxis="ksTotalsMonthlyCategoryAxis"
    :kValueAxis="ksTotalsMonthlyValueAxis"
    :kLegendVisible="true"
    :kSeriesLabelVisible="true"
    :kLabelValueFormat="'{0:n0}'"
    :kToolTip="ksTotalsPieToolTip"
  />
</template>

<script>

import KChart from '~/components/kendo/chart/Chart'

export default {
  name: "ExpensesByCategoryPie",
  props: ['gridData','summaryTypes'],
  components: {
    KChart,
  },
  data: () => ({
    loading: true,
    ksTotalsPie: null,
    ksTotalsMonthlyValueAxis: null,
    ksTotalsMonthlyCategoryAxis: null,
    ksTotalsPieToolTip: null,
  }),
  async created () {
    this.loading = false
    await this.updateChartData()
  },
  methods: {
    async updateChartData(options) {
      this.loading = true
      let chartSeriesTotalCostCategories = []
      for (let key in this.gridData) {
        chartSeriesTotalCostCategories.push (this.gridData[key]['month'])
      }
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
      let grandTotal = 0
      let costTotals = {}
      costTotals["contributors"] = 0
      costTotals["fuel"] = 0
      for (let key in this.summaryTypes) {
        let column = this.summaryTypes[key]
        costTotals[column] = 0
      }
      for (let key in this.gridData) {
        let row = this.gridData[key]
        grandTotal = grandTotal + row['total_cost']
        costTotals["fuel"] = costTotals["fuel"] + row['fuel']
        costTotals["contributors"] = costTotals["contributors"] + row['contributors']
        for (let key in this.summaryTypes) {
          let column = this.summaryTypes[key]
          costTotals[column] = costTotals[column] + row[column]
        }
      }
      let ksTotalsPieData = []
      for (let key2 in costTotals) {
        let per = (costTotals[key2] / grandTotal) * 100
        ksTotalsPieData.push({category: key2, value: per})
      }
      this.ksTotalsPie = [{data: ksTotalsPieData}]
      this.ksTotalsPieToolTip = {
        visible: true,
        template: "#= category # #= kendo.format('{0:n0}',value) #%"
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
