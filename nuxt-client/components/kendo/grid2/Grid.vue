<template>
  <KendoTooltip
    :content="getToolTipContent"
    :filter="'td'"
  >
    <Grid
      v-if="showGrid"
      :height="height"
      :data-source="dataSource"
      :columns="columns"
      :column-menu="columnMenu"
      :filterable-mode="filterableMode"
      :selectable="selectable"
      :resizable="true"
      :reorderable="true"
      :groupable="true"
      :navigatable="true"
      :sortable="true"
      :sortable-mode="sortableMode"
      :sortable-allow-unsort="true"
      :sortable-show-indexes="true"
      :editable="editable"
      :edit="edit"
      :save="save"
      :remove="remove"
      :pageable-page-sizes="pageablePageSizes"
      :pageable-button-count="pageButtonCount"
      :toolbar="toolBar"
      :excel-file-name="exportFileName"
      :excel-filterable="true"
      :pdf-all-pages="true"
      :pdf-avoid-links="true"
      :pdf-paper-size="'A4'"
      :pdf-margin="{ top: '2cm', left: '1cm', right: '1cm', bottom: '1cm' }"
      :pdf-landscape="true"
      :pdf-repeat-headers="true"
      :pdf-scale="0.8"
      :dataBound="dataBound"
    />
  </KendoTooltip>
</template>


<script>
import { kendo } from '@progress/kendo-ui'
import { Grid } from '@progress/kendo-grid-vue-wrapper'
import { KendoTooltip } from '@progress/kendo-popups-vue-wrapper'
import { GridInstaller } from '@progress/kendo-grid-vue-wrapper'
import $ from 'jquery'
import JSZip from 'jszip'

window.JSZip = JSZip;

//https://www.telerik.com/kendo-vue-ui/components/grid-wrapper/
//https://docs.telerik.com/kendo-ui/api/javascript/data/datasource  see datasource filter, sort, group,
//https://docs.telerik.com/kendo-ui/api/javascript/ui/grid#methods  see events

export default {
  props: ['kGridData', 'kSchema', 'kColumns', 'kColumnMenu', 'kGroup', 'kAggregate', 'kFilterableMode', 'kSelectable',
    'kToolBar', 'kPageSize', 'rowClicked', 'height', 'kExportFileName',
    'kGridRef', 'kEditable', 'kEdit', 'kSave','kRemove', 'kFilter', 'kSort'],
  components: {
    'Grid': Grid,
    'KendoTooltip': KendoTooltip,
  },
  data: () => ({
    dataSource: {},
    columns: null,
    columnMenu: true,
    group: null,
    aggregate: [],
    filterableMode: 'menu, row',
    selectable: true,
    sortableMode: 'multiple',
    pageSize: 20,
    pageablePageSizes: [10, 20, 50, 100, 200, 500, 1000, 10000, 100000],
    pageButtonCount: 3,
    toolBar: null,
    exportFileName: 'Export',
    showGrid: true,
    editable: false,
    edit: null,
    save: null,
    remove: null,
    dataBound: null,
  }),
  created() {
    this.columns = this.kColumns
    if (this.kGridRef) {
      this.ref = this.kGridRef
    }
    if (this.kColumnMenu) {
      this.columnMenu = this.kColumnMenu
    }
    if (this.kGroup) {
      this.group = this.kGroup
    }
    if (this.kAggregate) {
      this.aggregate = this.kAggregate
    }
    if (this.kFilterableMode) {
      this.filterableMode = this.kFilterableMode
    }
    if (this.kSelectable) {
      this.selectable = this.kSelectable
    }
    if (this.kEditable) {
      this.editable = this.kEditable
    }
    if (this.kEdit) {
      this.edit = this.kEdit
    }
    if (this.kSave) {
      this.save = this.kSave
    }
    if (this.kRemove) {
      this.remove = this.kRemove
    }
    if (this.kToolBar) {
      this.toolBar = this.kToolBar
    }
    if (this.kPageSize) {
      this.pageSize = this.kPageSize
    }
    if (this.kExportFileName) {
      this.exportFileName = this.kExportFileName
    }
    this.dataSource = {
      pageSize: this.pageSize,
      schema: this.kSchema,
      data: this.kGridData,
      aggregate: this.aggregate,
    }
    if (this.group) {
      this.dataSource.group = this.group
    }
    if (this.kFilter) {
      this.dataSource.filter = this.kFilter
    }
    if (this.kSort) {
      this.dataSource.sort = this.kSort
    }

    this.dataBound = this.gridChanged
  },

  methods: {
    gridChanged(e) {
      //https://docs.telerik.com/kendo-ui/api/javascript/data/datasource
      let tDataSource = e.sender.dataSource
      let tFilteredData = tDataSource.view()
      //let tFilteredAndGroupedData = tDataSource.fetch()
      this.$emit('grid-filtered-update', tFilteredData)
    },
    getToolTipContent (e) {
      let element = e.target[0]
      if (element.offsetWidth < element.scrollWidth) {
        document.querySelectorAll('[role="tooltip"]').forEach(el => {
          el.style.visibility = "visible";
        });
        return e.target.text()
      } else {
        document.querySelectorAll('[role="tooltip"]').forEach(el => {
          el.style.visibility = "hidden";
        });
      }
    },
    refresh () {
      this.showGrid = false
      this.$nextTick().then(() => {
        this.showGrid = true
      })
    },
  }
}

</script>


<style scoped>
</style>
