<template>
  <v-card class="mt-15">
    <v-row>
      <v-col cols="12">
        <v-card-title>
          Line Item Categories
        </v-card-title>
      </v-col>
      <v-col>
        <v-btn v-if="showAddLineItemCategoryInGrid" small color="info" style="float: right" class="my-4 mr-3" @click="addLineItemCategory">Add Line Item Category</v-btn>
      </v-col>
    </v-row>

    <div class="px-3 mb-5">
      <v-row>
        <v-col cols="4">
          <v-text-field
            v-model="filterByName"
            label="Search by name"
            dense
            @blur="getDataFromApi"
            v-on:keyup.enter="getDataFromApi"
          />
        </v-col>
        <v-col cols="4" v-if="!this.lineItemType">
          <ModelNameSearchSelect
            v-model="filteredLineItemType"
            dense
            :prefill="true"
            :search_label="'Search by Line Item Type'"
            :api_url_base="'line_item_types'"
          />
        </v-col>
        <v-col>
          <v-btn small color="primary" style="float: right" class="ml-5 mb-2" :loading="loading" @click="getDataFromApi">Search</v-btn>
          <v-btn small style="float: right;" class="ml-5 mb-2" :disabled="loading" @click="clearFilters">Clear</v-btn>
        </v-col>
      </v-row>
    </div>

    <v-data-table
      :headers="headers"
      :loading="loading"
      :items="tableData"
      item-key="id"
      :items-per-page="100"
      :footer-props="{ itemsPerPageOptions: [50, 100, 200, 500, -1] }"
      :server-items-length="totalItems"
      dense
      @update:options="getDataFromApi"
      @click:row="openData"
    >
      <template v-slot:item.created_at="{ item, value }">
        <template v-if="value">{{ value | formatDateMDY }}</template>
      </template>
      <template v-slot:item.updated_at="{ item, value }">
        {{ value | formatDateMDY }}
      </template>
      <template v-slot:item.actions="{ item }">
        <v-tooltip top>
          <template v-slot:activator="{ on, attrs }">
            <v-icon
              small
              class="mr-2"
              v-bind="attrs"
              v-on="on"
            >
              mdi-pencil
            </v-icon>
          </template>
          <span>Manage Line Item Category</span>
        </v-tooltip>
      </template>

    </v-data-table>

    <ManageLineItemCategoryComponent
      v-if="addingLineItemCategory"
      v-model="addingLineItemCategory"
      :lineItemType="lineItemType"
      @line_item_category-saved="getDataFromApi"
    />

  </v-card>

</template>

<script>

import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'
import ManageLineItemCategoryComponent from '~/components/line_item_categories/ManageLineItemCategory'

export default {
  name: "LineItemCategoriesGrid",
  props: ['value', 'lineItemType', 'showAddLineItemCategoryInGrid', 'showLineItemTypeInGrid'],
  components: {
    ManageLineItemCategoryComponent,
    ModelNameSearchSelect
  },
  data: () => ({
    loading: true,
    addingLineItemCategory: false,
    filterByName: null,
    filteredLineItemType: null,
    tableData:[],
    totalItems: 0,
    options: {page: 1},
  }),
  computed: {
    headers () {
      let headers = []
      // headers.push({ text: 'Line Item Category ID', align: 'left', value: 'id' })
      headers.push({ text: 'Name', align: 'left', value: 'name' })
      if (this.showLineItemTypeInGrid) {
        headers.push({text: 'Line Item Type', align: 'left', value: 'line_item_type_name'})
      }
      headers.push({ text: 'Created Date', align: 'left', value: 'created_at' })
      headers.push({ text: 'Updated Date', align: 'left', value: 'updated_at' })
      headers.push({ text: 'Actions', value: 'actions', sortable: false })
      return headers
    },
    parentLineItemType() {
      if (this.lineItemType) {
        return this.lineItemType
      }
      else {
        return null
      }
    }
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.tableData = []

      if (options) {
        this.options = options
      } else {
        options = { sortBy: null, sortDesc: null }
      }

      if (this.lineItemType && !this.filteredLineItemType) {
        this.filteredLineItemType = this.lineItemType
      }

      const response = await this.$axios.$get('line_item_categories', {
        params: {
          page: this.options.page,
          _sort: (options.sortBy) ? options.sortBy[0] : null,
          _sort_dir: (options.sortDesc && options.sortDesc[0] == true) ? 'desc' : 'asc',
          filterByName: this.filterByName,
          filterByLineItemType: (this.filteredLineItemType) ? this.filteredLineItemType.id : null,
        }
      })

      this.tableData = response.data
      this.totalItems = response.total

      this.loading = false
    },
    openData (dataItem) {
      this.$router.push(`/line_item_categories/${dataItem.id}`)
    },
    clearFilters () {
      this.filterByName = null
      this.filteredLineItemType = null
      this.getDataFromApi()
    },
    addLineItemCategory () {
      this.addingLineItemCategory = true
    }
  }
}
</script>

<style scoped>

</style>
