#pragma once

template <typename F>
struct ScopeExitFunctionWrapper {
	ScopeExitFunctionWrapper(const F & f) : f(f) {}

	ScopeExitFunctionWrapper(const ScopeExitFunctionWrapper &) = delete;
	ScopeExitFunctionWrapper(ScopeExitFunctionWrapper &&) = default;

	ScopeExitFunctionWrapper & operator = (const ScopeExitFunctionWrapper &) = delete;
	ScopeExitFunctionWrapper & operator = (ScopeExitFunctionWrapper &&) = default;

	~ScopeExitFunctionWrapper() { f(); }
	F f;
};

// костыль из-за невозможности сделать "Template argument deduction for class templates".
template <typename F>
static constexpr ScopeExitFunctionWrapper<F> CreateScopeExitFunctionWrapper (const F & f) {
	return ScopeExitFunctionWrapper<F> (f);
}

#define DO_STRING_JOIN2(arg1, arg2) arg1 ## arg2
#define STRING_JOIN2(arg1, arg2) DO_STRING_JOIN2(arg1, arg2)

#define SCOPE_EXIT(...) \
	auto STRING_JOIN2(scope_exit_, __LINE__) = ::CreateScopeExitFunctionWrapper(__VA_ARGS__); \
	(void)STRING_JOIN2(scope_exit_, __LINE__)
