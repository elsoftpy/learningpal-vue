import{B as N,s as R,f as q,o as l,c as x,m as z,r as C,a as u,u as I,b as T,d as F,e as O,g as U,h as d,w as c,i,j as y,k as s,t as a,l as n,n as H,p as K,q as M,v as G}from"./app-1iVevW2O.js";import{u as J}from"./useApiErrorHandler-CDsSMG_d.js";import{h as Q,u as W,s as X}from"./index-8RxIc6jn.js";import{o as Y,b as Z,s as P}from"./schemas-B7JmrYh_.js";import{s as ee}from"./index-DOk0eLiO.js";import{s as te}from"./index-DAbH8C3g.js";import{s as g}from"./index-B5zSQQrO.js";import{s as re}from"./index-BPV-gVr2.js";import"./index-BPuB08ed.js";const ie=t=>Y({name:P().min(1,t("Username is required")),password:P().min(1,t("Password is required")).min(6,t("Password must be at least 6 characters long.")),remember:Z().default(!1)});var ne=`
    .p-divider-horizontal {
        display: flex;
        width: 100%;
        position: relative;
        align-items: center;
        margin: dt('divider.horizontal.margin');
        padding: dt('divider.horizontal.padding');
    }

    .p-divider-horizontal:before {
        position: absolute;
        display: block;
        inset-block-start: 50%;
        inset-inline-start: 0;
        width: 100%;
        content: '';
        border-block-start: 1px solid dt('divider.border.color');
    }

    .p-divider-horizontal .p-divider-content {
        padding: dt('divider.horizontal.content.padding');
    }

    .p-divider-vertical {
        min-height: 100%;
        display: flex;
        position: relative;
        justify-content: center;
        margin: dt('divider.vertical.margin');
        padding: dt('divider.vertical.padding');
    }

    .p-divider-vertical:before {
        position: absolute;
        display: block;
        inset-block-start: 0;
        inset-inline-start: 50%;
        height: 100%;
        content: '';
        border-inline-start: 1px solid dt('divider.border.color');
    }

    .p-divider.p-divider-vertical .p-divider-content {
        padding: dt('divider.vertical.content.padding');
    }

    .p-divider-content {
        z-index: 1;
        background: dt('divider.content.background');
        color: dt('divider.content.color');
    }

    .p-divider-solid.p-divider-horizontal:before {
        border-block-start-style: solid;
    }

    .p-divider-solid.p-divider-vertical:before {
        border-inline-start-style: solid;
    }

    .p-divider-dashed.p-divider-horizontal:before {
        border-block-start-style: dashed;
    }

    .p-divider-dashed.p-divider-vertical:before {
        border-inline-start-style: dashed;
    }

    .p-divider-dotted.p-divider-horizontal:before {
        border-block-start-style: dotted;
    }

    .p-divider-dotted.p-divider-vertical:before {
        border-inline-start-style: dotted;
    }

    .p-divider-left:dir(rtl),
    .p-divider-right:dir(rtl) {
        flex-direction: row-reverse;
    }
`,oe={root:function(r){var e=r.props;return{justifyContent:e.layout==="horizontal"?e.align==="center"||e.align===null?"center":e.align==="left"?"flex-start":e.align==="right"?"flex-end":null:null,alignItems:e.layout==="vertical"?e.align==="center"||e.align===null?"center":e.align==="top"?"flex-start":e.align==="bottom"?"flex-end":null:null}}},ae={root:function(r){var e=r.props;return["p-divider p-component","p-divider-"+e.layout,"p-divider-"+e.type,{"p-divider-left":e.layout==="horizontal"&&(!e.align||e.align==="left")},{"p-divider-center":e.layout==="horizontal"&&e.align==="center"},{"p-divider-right":e.layout==="horizontal"&&e.align==="right"},{"p-divider-top":e.layout==="vertical"&&e.align==="top"},{"p-divider-center":e.layout==="vertical"&&(!e.align||e.align==="center")},{"p-divider-bottom":e.layout==="vertical"&&e.align==="bottom"}]},content:"p-divider-content"},se=N.extend({name:"divider",style:ne,classes:ae,inlineStyles:oe}),le={name:"BaseDivider",extends:R,props:{align:{type:String,default:null},layout:{type:String,default:"horizontal"},type:{type:String,default:"solid"}},style:se,provide:function(){return{$pcDivider:this,$parentInstance:this}}};function b(t){"@babel/helpers - typeof";return b=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(r){return typeof r}:function(r){return r&&typeof Symbol=="function"&&r.constructor===Symbol&&r!==Symbol.prototype?"symbol":typeof r},b(t)}function k(t,r,e){return(r=de(r))in t?Object.defineProperty(t,r,{value:e,enumerable:!0,configurable:!0,writable:!0}):t[r]=e,t}function de(t){var r=ce(t,"string");return b(r)=="symbol"?r:r+""}function ce(t,r){if(b(t)!="object"||!t)return t;var e=t[Symbol.toPrimitive];if(e!==void 0){var v=e.call(t,r);if(b(v)!="object")return v;throw new TypeError("@@toPrimitive must return a primitive value.")}return(r==="string"?String:Number)(t)}var $={name:"Divider",extends:le,inheritAttrs:!1,computed:{dataP:function(){return q(k(k(k({},this.align,this.align),this.layout,this.layout),this.type,this.type))}}},pe=["aria-orientation","data-p"],ue=["data-p"];function me(t,r,e,v,w,h){return l(),x("div",z({class:t.cx("root"),style:t.sx("root"),role:"separator","aria-orientation":t.layout,"data-p":h.dataP},t.ptmi("root")),[t.$slots.default?(l(),x("div",z({key:0,class:t.cx("content"),"data-p":h.dataP},t.ptm("content")),[C(t.$slots,"default")],16,ue)):u("",!0)],16,pe)}$.render=me;const ve={class:"w-full"},fe={class:"mt-4"},ye={for:"name",class:"block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"},ge={class:"mt-4"},be={for:"password",class:"block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"},he={class:"flex text-sm"},_e={class:"mt-6 flex items-center"},xe={for:"remember",class:"ml-2 text-sm font-medium text-gray-700 dark:text-gray-300"},ke={class:"mt-4"},we={class:"flex w-full justify-between mt-4"},Se={class:"flex w-full justify-between mt-4"},ze={key:0},Ne={__name:"LoginPage",setup(t){const{t:r}=I(),{handleApiError:e}=J(),v=M(),w=G(),h=T(),j=F(()=>ie(r)),B=Q(j.value),E=O(!0),V=U({name:"",password:"",remember:!1}),{errors:p,isLoading:_,setErrors:A,clearErrors:D}=W({name:"",password:"",general:""}),L=async({valid:S,values:m})=>{D(),_.value=!0;try{if(!S){_.value=!1;return}const f=await h.login(m),o=w.query.redirect||"/dashboard";v.push(o)}catch(f){const o=e(f);if(o?.silent)return;if(o?.type==="validation"&&o.errors){A(o.errors);return}p.general=o?.message||r("An unexpected error occurred. Please try again.")}finally{_.value=!1}};return(S,m)=>{const f=K("router-link");return l(),x("div",ve,[d(i(X),{resolver:i(B),initialValues:V,onSubmit:L,validateOnBlur:!0,class:"flex flex-col gap-4"},{default:c(o=>[i(p).general?(l(),y(i(g),{key:0,severity:"error",size:"small",variant:"outlined"},{default:c(()=>[s(a(i(p).general),1)]),_:1})):u("",!0),n("div",fe,[n("label",ye,[s(a(i(r)("Username"))+" ",1),m[0]||(m[0]=n("span",{class:"text-red-500"},"*",-1))]),d(i(ee),{id:"name",name:"name",type:"text",fluid:""}),o.name?.invalid?(l(),y(i(g),{key:0,severity:"error",size:"small",variant:"simple"},{default:c(()=>[s(a(o.name.error?.message),1)]),_:2},1024)):u("",!0),i(p)?.name?(l(),y(i(g),{key:1,severity:"error",size:"small",variant:"simple"},{default:c(()=>[s(a(i(p)?.name),1)]),_:1})):u("",!0)]),n("div",ge,[n("label",be,[s(a(i(r)("Password"))+" ",1),m[1]||(m[1]=n("span",{class:"text-red-500"},"*",-1))]),d(i(te),{id:"password",name:"password",feedback:!1,toggleMask:"",fluid:""}),o.password?.invalid?(l(),y(i(g),{key:0,severity:"error",size:"small",variant:"simple"},{default:c(()=>[s(a(o.password.error?.message),1)]),_:2},1024)):u("",!0),i(p).password?(l(),y(i(g),{key:1,severity:"error",size:"small",variant:"simple"},{default:c(()=>[s(a(i(p)?.password),1)]),_:1})):u("",!0)]),n("div",he,[n("div",_e,[d(i(H),{name:"remember",inputId:"remember"}),n("label",xe,a(i(r)("Remember me")),1)])]),n("div",ke,[d(i(re),{type:"submit",loading:i(_),label:i(r)("Log in"),class:"w-full",rounded:""},null,8,["loading","label"])])]),_:1},8,["resolver","initialValues"]),d(i($),{class:"my-8"}),n("div",we,[n("div",Se,[E.value?(l(),x("p",ze,[d(f,{to:{name:"forgot-password"},class:"text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"},{default:c(()=>[s(a(i(r)("Forgot your password?")),1)]),_:1})])):u("",!0),n("p",null,[d(f,{to:{name:"register"},class:"ml-4 text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"},{default:c(()=>[s(a(i(r)("Register")),1)]),_:1})])])])])}}};export{Ne as default};
