<template>
  <td v-if="rowType==='groupFooter'">
    {{computedAggregates}}
  </td>
  <td v-else :class="className">
    {{ getNestedValue(field, dataItem)}}
  </td>
</template>
<script>

import { provideIntlService } from "@progress/kendo-vue-intl"

export default {
  props: {
    field: String,
    dataItem: Object,
    format: String,
    className: String,
    columnIndex: Number,
    columnsCount: Number,
    rowType: String,
    level: Number,
    expanded: Boolean,
    editor: String,
  },
  inject: {
    kendoIntlService: { default: null },
  },
  computed: {
    computedAggregates: function() {
      let renderedString;
      if (this.dataItem.aggregates[this.field] != null) {
        if (this.dataItem.aggregates[this.field]["sum"]) {
          let sum = this.dataItem.aggregates[this.field]["sum"]
          if (this.format == "{0:c}") {
            sum = provideIntlService(this).formatNumber(sum, "c")
          }
          renderedString = ' Total: ' + sum
        }
        else if (this.dataItem.aggregates[this.field]["average"]) {
          let avg = this.dataItem.aggregates[this.field]["average"]
          if (this.format == '{0:n}') {
            avg = provideIntlService(this).formatNumber(avg, "n")
          }
          renderedString = ' Total: ' + avg
        }
      }
      return renderedString;
    }
  },
  created() {
  },
  methods: {
    onClick(e) {
      this.$emit('change', e, this.dataItem, this.expanded);
    },
    getNestedValue (fieldName, dataItem) {
      const path = fieldName.split('.');
      let data = dataItem;
      path.forEach((p) => {
        data = data ? data[p] : undefined;
      });
      if (this.format == "{0:c}") {
        data = provideIntlService(this).formatNumber(data, "c")
      }
      else if (this.format == "{0:d}") {
        data = provideIntlService(this).formatDate(data, "d")
      }
      return data;
    }
  }
}
</script>
