import { defineComponent, h } from 'vue';

export const useColumnRenderers = () => {
  const createSlotComponent = (renderer) =>
    defineComponent({
      props: {
        data: { type: Object, default: null },
        column: { type: Object, default: null },
        field: { type: [String, Number], default: null },
        index: { type: Number, default: null },
        filterModel: { type: Object, default: null },
        filterCallback: { type: Function, default: null },
      },
      setup(props) {
        return () => renderer(props, h);
      },
    });

  return {
    createSlotComponent,
  };
};
