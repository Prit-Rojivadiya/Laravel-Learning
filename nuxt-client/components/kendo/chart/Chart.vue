<template>
  <Chart
    :title-text="chartTitle"
    :legend-visible="legendVisible"
    :legend-position="'bottom'"
    :series-defaults-type="seriesDefaultsType"
    :series-defaults-style="seriesDefaultsStyle"
    :series-defaults-labels-visible="seriesDefaultLabelsVisible"
    :series-defaults-labels-template="seriesDefaultLabelsTemplate"
    :series="series"
    :category-axis="categoryAxis"
    :value-axis="valueAxis"
    :tooltip="tooltip"
    :theme="'sass'"
    />
</template>


<script>
import { kendo } from '@progress/kendo-ui'
import { Chart } from '@progress/kendo-charts-vue-wrapper'

export default {
  props: ['kSeries', 'kChartTitle', 'kCategoryAxis', 'kValueAxis', 'kLegendVisible', 'kLegendPosition', 'kSeriesType', 'kSeriesStyle', 'kSeriesLabelVisible', 'kSeriesLabelTemplate', 'kToolTip', 'kLabelValueFormat'],
  components: {
    Chart,
  },
  data: () => ({
    series: null,
    chartTitle: null,
    categoryAxis: null,
    valueAxis: null,
    legendVisible: false,
    legendPosition: 'bottom',
    seriesDefaultsType: 'line',
    seriesDefaultsStyle: 'smooth',
    seriesDefaultLabelsVisible: false,
    seriesDefaultLabelsTemplate: "#= category # #= kendo.format('{FMT}',value) #%",
    tooltip: {
      visible: true,
      template: "#= series.name #: #= kendo.format('{FMT}',value) #"
    }
  }),
  created() {
    this.series = this.kSeries
    if (this.kChartTitle) {
      this.chartTitle = this.kChartTitle
    }
    if (this.kCategoryAxis) {
      this.categoryAxis = this.kCategoryAxis
    }
    if (this.kValueAxis) {
      this.valueAxis = this.kValueAxis
    }
    if (this.kLegendVisible) {
      this.legendVisible = this.kLegendVisible
    }
    if (this.kLegendPosition) {
      this.legendPosition = this.kLegendPosition
    }
    if (this.kSeriesType) {
      this.seriesDefaultsType = this.kSeriesType
    }
    if (this.kSeriesStyle) {
      this.seriesDefaultsStyle = this.kSeriesStyle
    }
    if (this.kSeriesLabelVisible) {
      this.seriesDefaultLabelsVisible = this.kSeriesLabelVisible
    }
    if (this.kSeriesLabelTemplate) {
      this.seriesDefaultLabelsTemplate = this.kSeriesLabelTemplate
    }
    if (this.kLabelValueFormat) {
      this.tooltip.template = this.tooltip.template.replaceAll('{FMT}', this.kLabelValueFormat)
      this.seriesDefaultLabelsTemplate = this.seriesDefaultLabelsTemplate.replaceAll('{FMT}', this.kLabelValueFormat)
    }
    else {
      //set default format
      this.tooltip.template = this.tooltip.template.replaceAll('{FMT}', '{0:C}')
      this.seriesDefaultLabelsTemplate = this.seriesDefaultLabelsTemplate.replaceAll('{FMT}', '{0:n0}')
    }
    if (this.kToolTip) {
      this.tooltip = this.kToolTip
    }
  },
  methods: {
  }
}

</script>


<style scoped>
</style>
