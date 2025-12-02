<template>
  <transition
    @before-enter="beforeEnter"
    @enter="enter"
    @before-leave="beforeLeave"
    @leave="leave"
  >
    <div v-show="show" class="overflow-hidden">
      <slot></slot>
    </div>
  </transition>
</template>

<script setup>
const props = defineProps({
  show: Boolean
});

/*
  We animate BOTH HEIGHT and OPACITY
  so inner items always stay in sync.
*/

const beforeEnter = (el) => {
  el.style.height = "0px";
  el.style.opacity = "0";
};

const enter = (el) => {
  const h = el.scrollHeight;

  el.style.transition = "height 0.25s ease, opacity 0.25s ease";

  requestAnimationFrame(() => {
    el.style.height = h + "px";
    el.style.opacity = "1";
  });

  el.addEventListener(
    "transitionend",
    () => {
      el.style.height = "auto"; // unlock natural height
    },
    { once: true }
  );
};

const beforeLeave = (el) => {
  el.style.height = el.scrollHeight + "px";
  el.style.opacity = "1";
};

const leave = (el) => {
  el.style.transition = "height 0.25s ease, opacity 0.2s ease";
  
  requestAnimationFrame(() => {
    el.style.height = "0px";
    el.style.opacity = "0";
  });
};
</script>
