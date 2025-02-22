<template>
  <div class="data-table">
    <data-loading
      v-show="shouldShowLoader"
      :for="/tasks\?page|results\?page/"
      :empty="$t('Congratulations')"
      :empty-desc="$t('You don\'t currently have any tasks assigned to you')"
      empty-icon="beach"
    />
    <div
      v-show="!shouldShowLoader"
      class="card card-body tasks-table-card"
      data-cy="tasks-table"
    >
      <vuetable
        ref="vuetable"
        :data-manager="dataManager"
        :sort-order="sortOrder"
        :css="css"
        :api-mode="false"
        :fields="fields"
        :data="data"
        data-path="data"
        pagination-path="meta"
        @vuetable:pagination-data="onPaginationData"
      >
        <template
          slot="name"
          slot-scope="props"
        >
          <b-link
            v-uni-id="props.rowData.id.toString()"
            :href="onAction('edit', props.rowData, props.rowIndex)"
          >
            {{ props.rowData.element_name }}
          </b-link>
        </template>

        <template
          slot="requestName"
          slot-scope="props"
        >
          <b-link
            :href="
              onAction('showRequestSummary', props.rowData, props.rowIndex)
            "
          >
            #{{ props.rowData.process_request.id }}
            {{ props.rowData.process.name }}
          </b-link>
        </template>

        <template
          slot="status"
          slot-scope="props"
        >
          <i
            class="fas fa-circle small"
            :class="statusColor(props.rowData)"
          />
          {{ $t(statusLabel(props.rowData)) }}
        </template>

        <template
          slot="assignee"
          slot-scope="props"
        >
          <avatar-image
            v-if="props.rowData.user"
            size="25"
            :input-data="props.rowData.user"
            hide-name="true"
          />
        </template>

        <template
          slot="dueDate"
          slot-scope="props"
        >
          <span :class="classDueDate(props.rowData.due_at)">
            {{ formatDate(props.rowData.due_at) }}
          </span>
        </template>

        <template
          slot="completedDate"
          slot-scope="props"
        >
          <span class="text-dark">
            {{ formatDate(props.rowData.completed_at) }}
          </span>
        </template>

        <template
          slot="preview"
          slot-scope="props"
        >
          <span>
            <i
              class="fa fa-eye py-2"
              @click="previewTasks(props.rowData)"
            />
          </span>
        </template>

        <template
          slot="actions"
          slot-scope="props"
        >
          <ellipsis-menu
            :actions="actions"
            :data="props.rowData"
            :divider="false"
          />
        </template>
      </vuetable>
      <pagination
        ref="pagination"
        :single="$t('Task')"
        :plural="$t('Tasks')"
        :per-page-select-enabled="true"
        @changePerPage="changePerPage"
        @vuetable-pagination:change-page="onPageChange"
      />
    </div>
    <tasks-preview ref="preview" />
  </div>
</template>

<script>
import datatableMixin from "../../components/common/mixins/datatable";
import dataLoadingMixin from "../../components/common/mixins/apiDataLoading";
import EllipsisMenu from "../../components/shared/EllipsisMenu.vue";
import AvatarImage from "../../components/AvatarImage";
import isPMQL from "../../modules/isPMQL";
import moment from "moment";
import { createUniqIdsMixin } from "vue-uniq-ids";
import TasksPreview from "./TasksPreview.vue";
import ListMixin from "./ListMixin";

const uniqIdsMixin = createUniqIdsMixin();

Vue.component("AvatarImage", AvatarImage);
Vue.component("TasksPreview", TasksPreview);

export default {
  components: { EllipsisMenu },
  mixins: [datatableMixin, dataLoadingMixin, uniqIdsMixin, ListMixin],
  props: {
    filter: {},
    columns: {},
    pmql: {},
    savedSearch: {
      default: false,
    },
  },
  data() {
    return {
      actions: [
        {
          value: "edit",
          content: "Open Task",
          icon: "fas fa-caret-square-right",
          link: true,
          href: "/tasks/{{id}}/edit",
        },
        {
          value: "showRequestSummary",
          content: "Open Request",
          icon: "fas fa-clipboard",
          link: true,
          href: "/requests/{{process_request.id}}",
        },
      ],
      orderBy: "ID",
      order_direction: "DESC",
      status: "",
      sortOrder: [
        {
          field: "ID",
          sortField: "ID",
          direction: "DESC",
        },
      ],
      fields: [],
      previousFilter: "",
      previousPmql: "",
    };
  },
  computed: {
    endpoint() {
      if (this.savedSearch !== false) {
        return `saved-searches/${this.savedSearch}/results`;
      }

      return "tasks";
    },
  },
  mounted: function mounted() {
    this.setupColumns();
    const params = new URL(document.location).searchParams;
    const successRouting = params.get("successfulRouting") === "true";
    if (successRouting) {
      ProcessMaker.alert(this.$t("The request was completed."), "success");
    }
  },
  methods: {
    setupColumns() {
      const columns = this.getColumns();

      columns.forEach((column) => {
        const field = {
          title: () => this.$t(column.label),
        };

        switch (column.field) {
          case "task":
            field.name = "__slot:name";
            field.field = "element_name";
            field.sortField = "element_name";
            break;
          case "status":
            field.name = "__slot:status";
            field.sortField = "status";
            break;
          case "request":
            field.name = "__slot:requestName";
            field.sortField = "process_requests.id,process_requests.name";
            break;
          case "assignee":
            field.name = "__slot:assignee";
            field.field = "user";
            break;
          case "due_at":
            field.name = "__slot:dueDate";
            break;
          case "completed_at":
            field.name = "__slot:completedDate";
            break;
          default:
            field.name = column.field;
        }

        if (!field.field) {
          field.field = column.field;
        }

        if (column.format === "datetime" || column.format === "date") {
          field.callback = "formatDate";
        }

        if (column.sortable && !field.sortField) {
          field.sortField = column.field;
        }

        this.fields.push(field);
      });

      this.fields.push({
        name: "__slot:preview",
        title: "",
      });

      this.fields.push({
        name: "__slot:actions",
        title: "",
      });

      // this is needed because fields in vuetable2 are not reactive
      this.$nextTick(() => {
        this.$refs.vuetable.normalizeFields();
      });
    },
    getColumns() {
      if (this.$props.columns) {
        return this.$props.columns;
      }
      const columns = [
        {
          label: "Task",
          field: "task",
          sortable: true,
          default: true,
        },
        {
          label: "Status",
          field: "status",
          sortable: true,
          default: true,
        },
        {
          label: "Request",
          field: "request",
          sortable: true,
          default: true,
        },
        {
          label: "Assignee",
          field: "assignee",
          sortable: false,
          default: true,
        },
      ];

      if (this.status === "CLOSED") {
        columns.push({
          label: "Completed",
          field: "completed_at",
          sortable: true,
          default: true,
        });
      } else {
        columns.push({
          label: "Due",
          field: "due_at",
          sortable: true,
          default: true,
        });
      }
      return columns;
    },
    onAction(action, rowData, index) {
      let link = "";
      if (action === "edit") {
        link = `/tasks/${rowData.id}/edit`;
      }

      if (action === "showRequestSummary") {
        link = `/requests/${rowData.process_request.id}`;
      }
      return link;
    },
    statusColor(props) {
      const { status } = props;
      const isSelfService = props.is_self_service;
      if (status == "ACTIVE" && isSelfService) {
        return "text-warning";
      } if (status == "ACTIVE") {
        return "text-success";
      } if (status == "CLOSED") {
        return "text-primary";
      }
      return "text-secondary";
    },
    statusLabel(props) {
      const { status } = props;
      const isSelfService = props.is_self_service;
      if (status == "ACTIVE" && isSelfService) {
        return "Self Service";
      } if (status == "ACTIVE") {
        return "In Progress";
      } if (status == "CLOSED") {
        return "Completed";
      }
      return status;
    },
    classDueDate(value) {
      const dueDate = moment(value);
      const now = moment();
      const diff = dueDate.diff(now, "hours");
      return diff < 0
        ? "text-danger"
        : diff <= 1
          ? "text-warning"
          : "text-dark";
    },
    getTaskStatus() {
      const path = new URL(location.href);
      const status = path.searchParams.get("status");
      return status === null ? "ACTIVE" : status;
    },
    previewTasks(info) {
      this.$refs.preview.showSideBar(info, this.data.data, true);
    },
  },
};
</script>

<style>
.tasks-table-card {
  padding: 0;
}
</style>
