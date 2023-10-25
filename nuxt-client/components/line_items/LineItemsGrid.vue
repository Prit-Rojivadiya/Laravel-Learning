<template>
  <div>
    <v-card class="mt-15">
      <v-row>
        <v-col cols="4">
          <v-card-title>
            Line Items
            <span v-if="!gridDataLoaded || savingLineItem" class="ml-5 k-i-loading"/>
          </v-card-title>
        </v-col>
<!--        <v-col>-->
<!--          <v-btn v-if="showAddLineItemInGrid" small color="info" style="float: right" class="my-4 mr-3" @click="addLineItem">Add Line Item</v-btn>-->
<!--        </v-col>-->
      </v-row>
      <v-alert v-if="errors.length" class="error">
        <template v-for="error in errors">
          {{ error.msg }}<br />
        </template>
        Line Item field to update.  Please refresh the page and try again
      </v-alert>

      <KGrid
        class="compact-grid"
        v-if="gridDataLoaded"
        :height="700"
        :kGridData="gridData"
        :kSchema="gridSchema"
        :kColumns="gridColumns"
        :kAggregate="gridAggregate"
        :kEditable="gridEditable"
        :kEdit="gridEdit"
        :kToolBar="[{ name: 'create', text: 'Add Line Item' }]"
        :kPageSize="100"
        :kSave="gridSaveLineItem"
        :kRemove="gridDeleteLineItem"
        @force_refresh_grid="refreshGrid"
      />

      <ManageLineItemComponent
        v-if="addingLineItem"
        v-model="addingLineItem"
        :repair_order="repair_order"
        @line_item-saved="getDataFromApi"
        @refresh-total-price="refreshTotalPrice"
      />

    </v-card>
  </div>
</template>

<script>

import ManageLineItemComponent from '~/components/line_items/ManageLineItem'
import KGrid from "~/components/kendo/grid2/Grid"
import $ from 'jquery'

export default {
  name: "LineItemsGrid",
  props: ['value', 'repair_order', 'showAddLineItemInGrid'],
  components: {
    ManageLineItemComponent,
    KGrid,
  },
  data: () => ({
    loading: true,
    gridDataLoaded: false,
    addingLineItem: false,
    filteredRepairOrder: null,
    gridData:[],
    gridSchema: {},
    gridColumns: [],
    gridAggregate: [],
    gridEditable: false,
    kColumnWidthDefault: "225px",
    kDefaultContains: {cell: {operator: 'contains'}},
    savingLineItem: false,
    errors: [],
    line_item_categories: [],
    formValid: false,
    form: {
      repair_order_id: null,
      line_item_category_id: null,
      price: null,
      quantity: null,
      total_price: null,
    },
    kGridDropdown: {
      items: [],
      itemValue: "id",
      itemText: "cat_type_and_name",
      field: "line_item_category_id",
      label: null,
    },
  }),
  async created () {
    this.gridDataLoaded = false
    this.errors = []
    this.addingLineItem = false
    let tLineItemCategories = await this.$axios.$get(`/line_item_categories?paginate=false`)
    if (tLineItemCategories) {
      this.line_item_categories = tLineItemCategories
      this.kGridDropdown.items = this.line_item_categories
    }

    this.gridColumns = [
      { field: 'line_item_category', title: 'Category', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, editor: this.categoryDropDownEditor, template: "#=line_item_category.name#"},
      //{ field: 'line_item_category_name', title: 'Category', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault},
      { field: 'price', title: 'Price', format: "{0:c}", hidden: false, width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#"},
      { field: 'quantity', title: 'Quantity', format: "{0:n}",  hidden: false, width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'n')#"},
      { field: 'total_price', title: 'Total Price', format: "{0:c}",  hidden: false, hideMeOnEdit:true, width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#"},
      { field: 'total_price_calc', title: 'Total Price', hidden: true, editable:false, template: "#=kendo.toString(kendo.parseFloat(total_price_calc()), 'c')#"},
      //{ cell: 'actionsTemplate', title: 'Actions', width: '120px'},
      { command: [
        {
          name: "edit",
          text: {
            edit: "Edit",               // This is the localization for Edit button
            update: "Save",             // This is the localization for Update button
            cancel: "Cancel"    // This is the localization for Cancel button
          }
        },
        'destroy'
        ], title:"&nbsp;", width: this.kColumnWidthDefault},
    ]

    this.gridAggregate = [
      { field: 'line_item_category', aggregate: 'count' },
      //{ field: 'line_item_category_name', aggregate: 'count' },
      { field: 'price', aggregate: 'sum' },
      { field: 'quantity', aggregate: 'sum' },
      { field: 'total_price', aggregate: 'sum' },
    ]

    this.gridSchema = {
      model: {
        id: "id",
        fields: {
          line_item_category: { defaultValue: { id: null, name: ""}, validation: { required: true } },
          //line_item_category_name: {type: 'string', from: "line_item_category.name"},
          price: {type: 'number', validation: { required: true }},
          quantity: {type: 'number', defaultValue: 1, validation: { required: true }},
          total_price: {type: 'number', editable: false},
        },
        total_price_calc: function () {
          return this.get("quantity") * this.get("price");
        },
      }
    }

    this.gridEditable = {
      mode: "popup",
      window: {
        title: "Line Item",
      }
    }

    await this.getDataFromApi()

    // Add custom tool tip
    // let btn_cl = ".k-grid-toolbar .k-grid-add"
    // let btn_tooltip = "Tip: use keyboard shortcut ctrl + n"
    // $('.k-grid').kendoTooltip({
    //   filter: btn_cl,
    //   content: btn_tooltip
    // });
  },

  mounted() {
    window.addEventListener('keypress', (e) => {
      //ctrl + n
      if(e.ctrlKey === true && e.key === 'n'){
        $('.k-grid').find('.k-grid-add').click()
      }
    });
  },

  methods: {
    async getDataFromApi() {
      this.loading = true
      this.gridDataLoaded = false
      this.gridData = []

      if (this.repair_order && !this.filteredRepairOrder) {
        this.filteredRepairOrder = this.repair_order
      }

      let options = { sortBy: 'id', sortDesc: 'asc' }
      const response = await this.$axios.$get('line_items', {
        params: {
          _sort: (options.sortBy) ? options.sortBy : null,
          _sort_dir: (options.sortDesc && options.sortDesc[0] == true) ? 'desc' : 'asc',
          filterByRepairOrder: (this.filteredRepairOrder) ? this.filteredRepairOrder.id : null,
        }
      })

      for (let key in response.data) {
        let item = response.data[key]
        item.price = parseFloat(item.price)
        item.quantity = parseFloat(item.quantity)
        item.total_price = parseFloat(item.total_price)
      }

      this.gridData = response.data
      this.loading = false
      this.gridDataLoaded = true
    },
    openData (dataItem) {
      this.$router.push(`/line_items/${dataItem.id}`)
    },
    clearFilters () {
      this.filterByRepairOrder = null
      this.getDataFromApi()
    },
    addLineItem () {
      this.addingLineItem = true
    },
    editLineItem (dataItem) {
      this.$router.push(`/line_items/${dataItem.id}`)
    },
    async saveLineItem (line_item) {
      this.savingLineItem = true
      this.errors = []
      this.form.repair_order_id = this.repair_order.id
      this.form.line_item_category_id = line_item.line_item_category.id
      this.form.price = line_item.price
      this.form.quantity = line_item.quantity
      this.form.total_price = line_item.total_price

      try {
        let savedLineItem
        if (line_item && line_item.id > 0) {
          savedLineItem = await this.$axios.$put(`/line_items/${line_item.id}`, this.form)
        } else {
          savedLineItem = await this.$axios.$post(`/line_items`, this.form)
        }
        this.$emit('line_item-saved', savedLineItem)
        this.savingLineItem = false
        this.refreshGrid()
      } catch (e) {
        console.log("my error", e.message)
        if (e.response && e.response.status == 422) {
          this.errors = e.response.parsedErrors
        }
        else {
          this.errors.push ({msg: e})
        }
        this.savingLineItem = false
      }
    },
    refreshGrid () {
      this.getDataFromApi()
    },
    refreshTotalPrice (savedLineItem) {
      this.$emit('refresh-total-price', savedLineItem)
    },

    categoryDropDownEditor(container, options) {
      let categoryDropDownDataSource = {
        data: this.line_item_categories,
      }
      $('<input required data-bind="value:' + options.field + '"/>')
        .appendTo(container)
        .kendoComboBox({
          dataSource: categoryDropDownDataSource,
          dataTextField: "name",
          dataValueField: "id",
          filter: "contains",
        });
    },

    gridEdit(e) {

      //On grid edit, auto focus on the first input field
      e.container.data('kendoWindow').bind('activate',function(e){
        //body > div.k-widget.k-window.k-display-inline-flex.k-state-focused > div.k-popup-edit-form.k-window-content > div > div:nth-child(2) > span
        $("input[type=text][role=combobox]").focus();
      })

      //force curser to the end of input fields on tab or input field focus
      $("input[type=text]").on("focus", function () {
        let input = $(this);
        clearTimeout(input.data("selectTimeId")); //stop started time out if any
        let selectTimeId = setTimeout(function()  {
          let len = input[0].value.length;
          input[0].setSelectionRange(len, len);
        });
        input.data("selectTimeId", selectTimeId);
      }).blur(function(e) {
        clearTimeout($(this).data("selectTimeId")); //stop started timeout
      });

      //Allow "enter" key when in grid edit mode to click save button
      $('.k-edit-field .k-input').on('keypress', function (e) {
        var key = e.which;
        if(key == 13)  // the enter key code
        {
          $('.k-grid-update').focus().trigger('click');
        }
      });

      //Hide specific columns on grid popup mode edit window
      e.sender.columns.forEach(function (element, index /*, array */) {
        if (element.hideMeOnEdit) {
          e.container.find(".k-edit-label:eq(" + index + "), "
            + ".k-edit-field:eq( " + index + ")"
          ).hide();
        }
      });

      //Make  read only
      $('[name="total_price_calc"]').attr("readonly", true);
    },

    gridSaveLineItem(e) {
      e.model.total_price = e.model.quantity * e.model.price
      if(e.model.id) {
        this.saveLineItem(e.model)
      }
      else {
        this.saveLineItem(e.model)
      }
    },

    gridDeleteLineItem(e) {
      let lineItemToDelete = e.model
      try {
        if (lineItemToDelete && lineItemToDelete.id > 0) {
          this.$axios.$delete(`line_items/${lineItemToDelete.id}`)
        }
        else {
          this.errors.push({msg: "Failed to delete line item.  Error: Missing id"})
        }
        this.$emit('line_item-saved', lineItemToDelete)
        this.refreshTotalPrice(lineItemToDelete)
        this.savingLineItem = false
      } catch (e) {
        console.log("my error", e.message)
        if (e.response && e.response.status == 422) {
          this.errors = e.response.parsedErrors
        }
        else {
          this.errors.push ({msg: e})
        }
        this.savingLineItem = false
      }
    }

  }
}
</script>

<style scoped>

</style>
