<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Line Item Type</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editLineItemType">Edit</v-btn>
          <v-dialog
            v-model="deleteDialog"
            persistent
            max-width="290"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-btn small
                     class="mr-5"
                     color="#fe8181"
                     style="float:right"
                     v-bind="attrs"
                     v-on="on">
                Delete
              </v-btn>
            </template>
            <v-card>
              <v-card-title class="headline">Are you sure?</v-card-title>
              <v-card-text>Do you really want to delete?</v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="green darken-1" text @click="deleteDialog = false">Cancel</v-btn>
                <v-btn color="red" @click="deleteItem">Delete</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

        </v-col>
      </v-row>
    </div>

    <v-card>
      <v-card-title>
        {{ line_item_type.name }}
      </v-card-title>
      <v-card-text>
        <LineItemTypeOverviewComponent
          v-if="!loading"
          :line_item_type="line_item_type"
          v-model="line_item_type"
        />
      </v-card-text>
    </v-card>

    <ManageLineItemTypeComponent
      v-if="showEditLineItemType"
      :line_item_type="line_item_type"
      v-model="showEditLineItemType"
      @line_item_type-saved="getDataFromApi"
    />

    <LineItemCategoriesGridComponent
      v-if="showLineItemCategories"
      :lineItemType="line_item_type"
      :showAddLineItemCategoryInGrid="showAddLineItemCategoryInGrid"
      :showLineItemTypeInGrid = "showLineItemTypeInGrid"
      v-model="showLineItemCategories"
    />


    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import LineItemTypeOverviewComponent from '~/components/line_item_types/Overview'
import ManageLineItemTypeComponent from '~/components/line_item_types/ManageLineItemType'
import LineItemCategoriesGridComponent from '~/components/line_item_categories/LineItemCategoriesGrid'



export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    LineItemTypeOverviewComponent,
    ManageLineItemTypeComponent,
    LineItemCategoriesGridComponent
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditLineItemType: false,
    showLineItemCategories: false,
    showAddLineItemCategoryInGrid: true,
    showLineItemTypeInGrid: true,

    line_item_type: {
      name: null,
      code: null,
      desc: null,
    },
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.line_item_type = await this.$axios.$get(`line_item_types/${this.$route.params.id}`)
      this.loading = false
      this.showLineItemCategories = true
    },
    editLineItemType () {
      this.showEditLineItemType = true
    },
    async deleteItem () {
      await this.$axios.$delete(`line_item_types/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
