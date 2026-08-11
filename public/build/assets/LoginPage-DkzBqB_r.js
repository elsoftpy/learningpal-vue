import{B as R,s as C,f as I,o as l,c as k,m as P,r as T,a as c,u as F,b as M,d as O,e as $,g as U,h as H,i as v,w as d,j as s,t as o,k as i,l as u,n,p as K,q as G,v as J,x as Q}from"./app-S1kVU4gk.js";import{u as W}from"./useApiErrorHandler-CbZMhpU0.js";import{h as X,u as Y,s as Z}from"./index-C1Nnea0a.js";import{o as ee,b as te,s as j}from"./schemas-B7JmrYh_.js";import{s as re}from"./index-agitlIKo.js";import{s as ie}from"./index-DZ8LsGhb.js";import{s as f}from"./index-DmnAaqUo.js";import{s as ne}from"./index-BtayDIVq.js";import"./index-DgJZXFSg.js";const ae=t=>ee({name:j().min(1,t("Username is required")),password:j().min(1,t("Password is required")).min(6,t("Password must be at least 6 characters long.")),remember:te().default(!1)});var oe=`
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
`,se={root:function(r){var e=r.props;return{justifyContent:e.layout==="horizontal"?e.align==="center"||e.align===null?"center":e.align==="left"?"flex-start":e.align==="right"?"flex-end":null:null,alignItems:e.layout==="vertical"?e.align==="center"||e.align===null?"center":e.align==="top"?"flex-start":e.align==="bottom"?"flex-end":null:null}}},le={root:function(r){var e=r.props;return["p-divider p-component","p-divider-"+e.layout,"p-divider-"+e.type,{"p-divider-left":e.layout==="horizontal"&&(!e.align||e.align==="left")},{"p-divider-center":e.layout==="horizontal"&&e.align==="center"},{"p-divider-right":e.layout==="horizontal"&&e.align==="right"},{"p-divider-top":e.layout==="vertical"&&e.align==="top"},{"p-divider-center":e.layout==="vertical"&&(!e.align||e.align==="center")},{"p-divider-bottom":e.layout==="vertical"&&e.align==="bottom"}]},content:"p-divider-content"},de=R.extend({name:"divider",style:oe,classes:le,inlineStyles:se}),ue={name:"BaseDivider",extends:C,props:{align:{type:String,default:null},layout:{type:String,default:"horizontal"},type:{type:String,default:"solid"}},style:de,provide:function(){return{$pcDivider:this,$parentInstance:this}}};function b(t){"@babel/helpers - typeof";return b=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(r){return typeof r}:function(r){return r&&typeof Symbol=="function"&&r.constructor===Symbol&&r!==Symbol.prototype?"symbol":typeof r},b(t)}function S(t,r,e){return(r=ce(r))in t?Object.defineProperty(t,r,{value:e,enumerable:!0,configurable:!0,writable:!0}):t[r]=e,t}function ce(t){var r=pe(t,"string");return b(r)=="symbol"?r:r+""}function pe(t,r){if(b(t)!="object"||!t)return t;var e=t[Symbol.toPrimitive];if(e!==void 0){var y=e.call(t,r);if(b(y)!="object")return y;throw new TypeError("@@toPrimitive must return a primitive value.")}return(r==="string"?String:Number)(t)}var B={name:"Divider",extends:ue,inheritAttrs:!1,computed:{dataP:function(){return I(S(S(S({},this.align,this.align),this.layout,this.layout),this.type,this.type))}}},me=["aria-orientation","data-p"],ve=["data-p"];function fe(t,r,e,y,h,_){return l(),k("div",P({class:t.cx("root"),style:t.sx("root"),role:"separator","aria-orientation":t.layout,"data-p":_.dataP},t.ptmi("root")),[t.$slots.default?(l(),k("div",P({key:0,class:t.cx("content"),"data-p":_.dataP},t.ptm("content")),[T(t.$slots,"default")],16,ve)):c("",!0)],16,me)}B.render=fe;const ye={class:"w-full"},ge={class:"mt-4"},be={for:"name",class:"block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"},he={class:"mt-4"},_e={for:"password",class:"block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"},xe={class:"flex text-sm"},ke={class:"mt-6 flex items-center"},we={for:"remember",class:"ml-2 text-sm font-medium text-gray-700 dark:text-gray-300"},Se={class:"mt-4"},ze={class:"flex w-full justify-between mt-4"},Pe={class:"flex w-full justify-between mt-4"},$e={key:0},Re={__name:"LoginPage",setup(t){const{t:r}=F(),{handleApiError:e}=W(),y=Q(),h=G(),_=M(),E=O(()=>ae(r)),q=X(E.value),V=$(!0),w=$(""),A=U({name:"",password:"",remember:!1}),{errors:p,isLoading:x,setErrors:D,clearErrors:L}=Y({name:"",password:"",general:""});H(()=>{w.value=typeof h.query.status=="string"?h.query.status:""});const N=async({valid:z,values:m})=>{L(),x.value=!0;try{if(!z){x.value=!1;return}const g=await _.login(m),a=h.query.redirect||"/dashboard";y.push(a)}catch(g){const a=e(g);if(a?.silent)return;if(a?.type==="validation"&&a.errors){D(a.errors);return}p.general=a?.message||r("An unexpected error occurred. Please try again.")}finally{x.value=!1}};return(z,m)=>{const g=J("router-link");return l(),k("div",ye,[w.value?(l(),v(i(f),{key:0,severity:"success",size:"small",variant:"outlined"},{default:d(()=>[s(o(w.value),1)]),_:1})):c("",!0),u(i(Z),{resolver:i(q),initialValues:A,onSubmit:N,validateOnBlur:!0,class:"flex flex-col gap-4"},{default:d(a=>[i(p).general?(l(),v(i(f),{key:0,severity:"error",size:"small",variant:"outlined"},{default:d(()=>[s(o(i(p).general),1)]),_:1})):c("",!0),n("div",ge,[n("label",be,[s(o(i(r)("Username"))+" ",1),m[0]||(m[0]=n("span",{class:"text-red-500"},"*",-1))]),u(i(re),{id:"name",name:"name",type:"text",fluid:""}),a.name?.invalid?(l(),v(i(f),{key:0,severity:"error",size:"small",variant:"simple"},{default:d(()=>[s(o(a.name.error?.message),1)]),_:2},1024)):c("",!0),i(p)?.name?(l(),v(i(f),{key:1,severity:"error",size:"small",variant:"simple"},{default:d(()=>[s(o(i(p)?.name),1)]),_:1})):c("",!0)]),n("div",he,[n("label",_e,[s(o(i(r)("Password"))+" ",1),m[1]||(m[1]=n("span",{class:"text-red-500"},"*",-1))]),u(i(ie),{id:"password",name:"password",feedback:!1,toggleMask:"",fluid:""}),a.password?.invalid?(l(),v(i(f),{key:0,severity:"error",size:"small",variant:"simple"},{default:d(()=>[s(o(a.password.error?.message),1)]),_:2},1024)):c("",!0),i(p).password?(l(),v(i(f),{key:1,severity:"error",size:"small",variant:"simple"},{default:d(()=>[s(o(i(p)?.password),1)]),_:1})):c("",!0)]),n("div",xe,[n("div",ke,[u(i(K),{name:"remember",inputId:"remember"}),n("label",we,o(i(r)("Remember me")),1)])]),n("div",Se,[u(i(ne),{type:"submit",loading:i(x),label:i(r)("Log in"),class:"w-full",rounded:""},null,8,["loading","label"])])]),_:1},8,["resolver","initialValues"]),u(i(B),{class:"my-8"}),n("div",ze,[n("div",Pe,[V.value?(l(),k("p",$e,[u(g,{to:{name:"forgot-password"},class:"text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"},{default:d(()=>[s(o(i(r)("Forgot your password?")),1)]),_:1})])):c("",!0),n("p",null,[u(g,{to:{name:"register"},class:"ml-4 text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"},{default:d(()=>[s(o(i(r)("Register")),1)]),_:1})])])])])}}};export{Re as default};
