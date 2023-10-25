<template>
<!--  :take="take"-->
<!--  :skip="skip"-->

  <pdfexport ref="gridPdfExport">
    <Grid
      v-if="refreshGrid"
      ref="grid"
      :style="style"
      :columns="columns"
      :data-items="gridData"
      :groupable="{footer: 'visible'}"
      :group="group"
      :expand-field="expandField"
      :sortable="true"
      :sort="sort"
      :resizable="true"
      :reorderable="true"
      :column-menu="columnMenu"
      :pageable="pageable"
      :skip="skip"
      :take="take"
      :filterable="filterable"
      :filter="filter"
      :selectable="selectable"
      @datastatechange="dataStateChange"
      @expandchange="expandChange"
      @rowclick="rowClick"
      @itemchange="itemChange"
      :edit-field="'inEdit'"
      :selected-field="selectedField"
    >
      <grid-toolbar>
        <v-btn v-if="showAddItem" small color="info" style="float: right" class="my-4 mr-3" @click="insert">{{showAddItemLabel}}</v-btn>
        <v-btn v-if="showExportPDF" small color="info" style="float: right" class="my-4 mr-3" @click="exportPDF">Export To PDF</v-btn>
        <v-btn v-if="showExportExcel" small color="info" style="float: right" class="my-4 mr-3" @click="exportExcel">Export To Excel</v-btn>
      </grid-toolbar>
      <template v-slot:columnMenu="{ props }">
        <ColumnMenu
          :column="props.column"
          :filterable="props.filterable"
          :filter="props.filter"
          :sortable="props.sortable"
          :sort="props.sort"
          :columns="columns"
          @closemenu="
            () => {
              props.onClosemenu()
            }
          "
          @columnssubmit="onColumnsSubmit"
        />
      </template>
      <template v-slot:actionsTemplate="{props}">
        <Commands :data-item="props.dataItem"
                @edit="edit"
                @save="save"
                @remove="remove"
                @cancel="cancel"/>
      </template>
      <template v-slot:dropDownCell="{props}">
        <DropDownCell
          :dataItem="props.dataItem"
          :kGridDropdown="kGridDropdown"
          @change="(e) => ddChange(e, props.dataItem)"
        />
      </template>
      <template v-slot:generalCell="{props}">
        <CellComponent
          :dataItem="props.dataItem"
          :field="props.field"
          :className="props.className"
          :rowType="props.rowType"
          :format="props.format"
        />
      </template>
    </Grid>
  </pdfexport>
</template>


<script>
import { Grid, GridToolbar } from '@progress/kendo-vue-grid'
import { Button } from '@progress/kendo-vue-buttons'
import { process } from '@progress/kendo-data-query'
import { GridPdfExport } from "@progress/kendo-vue-pdf"
import { saveExcel } from "@progress/kendo-vue-excel-export"
import { aggregateBy } from '@progress/kendo-data-query'
import ColumnMenu from '~/components/kendo/grid/ColumnMenu'
import Commands from '~/components/kendo/grid/Commands'
import DropDownCell from '~/components/kendo/grid/DropDownCell'
import CellComponent from '~/components/kendo/grid/CellComponent'
import {provideIntlService} from "@progress/kendo-vue-intl"


export default {
  props: ['kGridData', 'kColumns', 'kFilter', 'kGroup', 'kGridDropdown', 'kColumnMenu',
    'filterable','selectable','aggregates','showAddItem','showAddItemLabel','editOnRowClick',
    'kShowExportExcel','kShowExportPDF','kExportFileName','kTake','kGridStyle'],
  components: {
    'Grid': Grid,
    'grid-toolbar': GridToolbar,
    'kbutton': Button,
    'pdfexport': GridPdfExport,
    ColumnMenu,
    Commands,
    DropDownCell,
    CellComponent,
  },
  data: () => ({
    style: null,
    gridData: [],
    refreshGrid: false,
    pageable: {
      pageSizes: [10, 20, 50, 100, 200, 500, 1000, 10000, 100000],
      input: true,
      numeric: false
    },
    filter: null,
    skip: 0,
    take: 20,
    group: [],
    sort: [],
    expandedItems: [],
    selectedField: "selected",
    expandField: "expanded",
    columnMenu: true,
    showExportPDF: false,
    showExportExcel: false,
    exportFileName: "Export",
    totals: null,
    updatedData: [], //for inline grid data entry
  }),
  created() {
    this.columns = this.kColumns
    this.filter = this.kFilter
    this.group = this.kGroup
    if (this.kColumnMenu) {
      this.columnMenu = this.kColumnMenu
    }
    if (this.kShowExportPDF) {
      this.showExportPDF = this.kShowExportPDF
    }
    if (this.kShowExportExcel) {
      this.showExportExcel = this.kShowExportExcel
    }
    if (this.kExportFileName) {
      this.exportFileName = this.kExportFileName
    }
    if (this.kTake) {
      this.take = this.kTake
    }
    if (this.group) {
      this.group.map(group => group.aggregates = this.aggregates)
    }
    if (this.kGridStyle) {
      this.style = this.kGridStyle
    }
    this.getData(this.kGridData)
  },
  computed: {
    hasItemsInEdit() {
      let itemsInEdit = false
      for (let key in this.gridData.data) {
        if (this.gridData.data[key].inEdit == true) {
          itemsInEdit = true
          break
        }
      }
      return itemsInEdit
    }
  },
  methods: {
    getData(data) {
      this.gridData = process(data, {
        take: this.take,
        skip: this.skip,
        group: this.group,
        sort: this.sort,
        filter: this.filter,
      })
      let filterOnlyData = process(data, {
        filter: this.filter,
      })
      this.totals = aggregateBy(filterOnlyData.data, this.aggregates);
      if (this.totals != null) {
        for (let key in this.columns) {
          let field = this.columns[key]['field']
          if (this.totals[field] != null) {
            if (this.totals[field]['sum'] != null) {
              this.columns[key]['footer'] = this.footerSumTemplate //Used in Excel Export
              this.columns[key]['groupFooter'] = this.groupFooterTemplateSum //Used in Grid group footer
              this.columns[key]['footerCell'] = this.footerCellSumTemplate //Used in Grid footer
            } else if (this.totals[field]['average'] != null) {
              this.columns[key]['footer'] = this.footerAvgTemplate
              this.columns[key]['groupFooter'] = this.groupFooterTemplateAvg
              this.columns[key]['footerCell'] = this.footerCellAvgTemplate
            }
          }
        }
      }

      this.refreshGrid = true
      this.$emit('grid-filtered-update', filterOnlyData)

    },
    createAppState (dataState) {
      if (dataState.group) {
        dataState.group.map(group => group.aggregates = this.aggregates)
      }
      this.group = dataState.group
      this.take = dataState.take;
      this.skip = dataState.skip;
      this.filter = dataState.filter
      this.sort = dataState.sort
      this.getData(this.kGridData)
    },
    dataStateChange (event) {
      this.createAppState(event.data)
    },
    expandChange (event) {
      event.dataItem[event.target.$props.expandField] = event.value
      this.refresh()
    },
    onColumnsSubmit(columnsState) {
      this.columns = columnsState
      this.refresh()
    },
    rowClick (e) {
      if (this.editOnRowClick && !e.dataItem.inEdit) {
        this.edit(e)
      }
      //disable for now
      //e.dataItem.inEdit = true
    },
    ddChange (changedItemValue, dataItem) {
      let changeItem = {
        dataItem: dataItem,
        field: this.kGridDropdown.field,
        value: changedItemValue,
      }
      this.itemChange(changeItem)
    },
    itemChange (e) {
      //individual cell input changed
      let updatedData  = this.gridData.data
      let item = this.update(updatedData, e.dataItem) //item is reference pointer to the dataItem in updatedData array
      item[e.field] = e.value //this updates the item within the updatedData array,
      this.gridData.data = updatedData
      this.refresh()
    },
    insert() {
      let newItemTmpId = -1
      //check if other new items have already been added to the grid
      for (let i in this.gridData.data) {
        if (this.gridData.data[i].id <= newItemTmpId) {
          newItemTmpId = this.gridData.data[i].id - 1
        }
      }
      let dataItem = { id: newItemTmpId, inEdit: true }
      let newItemsArray = this.gridData.data
      newItemsArray.unshift(dataItem) //add dataItem to the beginning of the array
      let item = this.update(newItemsArray, dataItem)
      this.gridData = {
        data: newItemsArray,
        total: newItemsArray.length
      }
      this.refresh()
    },
    update (data, item, remove) {
      let index = -1
      for (let i in data) {
        if (data[i].id == item.id) {
          index = i
          break
        }
      }
      if (index >= 0) {
        data[index] = Object.assign({}, item) //copies data from source to target (item -> {})
      }
      if (remove) {
        data = data.splice(index, 1)
      }
      return data[index]
    },
    edit (e) {
      //TODO: Inline edit not yet implemented.  For now, open model view
      //e.dataItem.inEdit = true
      //this.refresh()
      if (this.editOnRowClick && !e.dataItem.inEdit && e.dataItem.id > 0) {
        this.$emit('model_item-edit', e.dataItem)
      }
      else {
        let text = "The table needs to refresh before this item can be edited.  Do you wish to proceed?"
        if (confirm(text) == true) {
          this.$emit('force_refresh_grid')
        }
      }
    },
    save (e) {
      e.dataItem.inEdit = undefined
      this.$emit('model_item-save', e.dataItem)
      this.refresh()
    },
    remove(e) {
      //TODO: Not yet implemented.  For now, inline delete model is not allowed
    },
    cancel(e) {
      let updatedData  = this.gridData.data
      e.dataItem.inEdit = undefined
      this.update(updatedData, e.dataItem, true)
      this.gridData = {
        data: updatedData,
        total: updatedData.length
      }
      this.refresh()
    },
    refresh () {
      this.refreshGrid = false
      this.$nextTick().then(() => {
        this.refreshGrid = true
      })
    },
    footerSumTemplate(props) {
      let value = 0;
      if (this.totals[props.column.field] != null) {
        value = this.totals[props.column.field].sum
      }
      value = this.formatFooter(value, props.column.field)
      return (`Total: ${value}`)
    },
    footerAvgTemplate(props) {
      let value = 0;
      if (this.totals[props.column.field] != null) {
        value = this.totals[props.column.field].average
      }
      value = this.formatFooter(value, props.column.field)
      return (`Avg: ${value}`)
    },
    footerCellSumTemplate(h, emptyElement, props, listeners) {
      let value = 0;
      if (this.totals[props.field] != null) {
        value = this.totals[props.field].sum
      }
      value = this.formatFooter(value, props.field)
      return h('span', {}, [`Total: ${value}`])
    },
    footerCellAvgTemplate(h, emptyElement, props, listeners) {
      let value = 0;
      if (this.totals[props.field] != null) {
        value = this.totals[props.field].average
      }
      value = this.formatFooter(value, props.field)
      return h('span', {}, [`Avg: ${value}`])
    },
    groupFooterTemplateSum(props) {
      let value = 0;
      if (this.totals[props.column.field] != null) {
        value = props.aggregates[props.column.field].sum
      }
      value = this.formatFooter(value, props.column.field)
      return (`Total: ${value}`)
    },
    groupFooterTemplateAvg(props) {
      let value = 0;
      if (this.totals[props.column.field] != null) {
        value = props.aggregates[props.column.field].average
      }
      value = this.formatFooter(value, props.column.field)
      return (`Total: ${value}`)
    },
    exportExcel() {
      const columnsToExport = [...this.columns];
      saveExcel({
        data: this.gridData.data,
        group: this.group,
        sort: this.sort,
        filter: this.filter,
        fileName: this.exportFileName,
        columns: columnsToExport,
      });
    },
    exportPDF() {
      this.$nextTick(() => {
        this.columnMenu = false;
        this.$refs.gridPdfExport.save(
          process(this.gridData.data,
            {
              group: this.group,
              sort: this.sort,
              filter: this.filter,
            })
        );
        this.columnMenu = true;
      });
    },
    formatFooter(value, field) {
      for (let key in this.columns) {
        let column = this.columns[key]
        if (column['field'] === field) {
          if (column['format'] == "{0:c}") {
            value = provideIntlService(this).formatNumber(value, "c")
            break;
          }
          else if (column['format'] == "{0:n}") {
            value = provideIntlService(this).formatNumber(value, "n2")
            break;
          }
        }
      }
      return value
    }
  }
}

</script>


<style scoped>
</style>
