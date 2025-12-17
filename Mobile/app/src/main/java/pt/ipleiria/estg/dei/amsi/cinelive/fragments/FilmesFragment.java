package pt.ipleiria.estg.dei.amsi.cinelive.fragments;

import android.content.Intent;
import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.SearchView;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.GridLayoutManager;

import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.activities.ConfiguracoesActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.DetalhesFilmeActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.MainActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.FilmesAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentFilmesBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.FilmesListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.DataManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorUtils;

public class FilmesFragment extends Fragment {
    private FragmentFilmesBinding binding;
    private DataManager manager;
    private FilmesAdapter adapter;
    private SearchView searchView;
    private DataManager.FilterFilmes filter = DataManager.FilterFilmes.EM_EXIBICAO;
    private boolean hasFilmes = false;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);

        // Obter o manager
        manager = DataManager.getInstance();
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentFilmesBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onCreateOptionsMenu(@NonNull Menu menu, @NonNull MenuInflater inflater) {
        super.onCreateOptionsMenu(menu, inflater);

        inflater.inflate(R.menu.menu_pesquisa, menu);
        MenuItem itemPesquisa = menu.findItem(R.id.itemPesquisa);

        itemPesquisa.setVisible(hasFilmes);

        // Obter SearchView
        searchView = (SearchView) itemPesquisa.getActionView();
        searchView.setQueryHint(getString(R.string.searchview_pesquisar_filmes));

        searchView.setOnQueryTextListener(new SearchView.OnQueryTextListener() {
            @Override
            public boolean onQueryTextSubmit(String query) {
                if (adapter != null) adapter.search(query);
                return true;
            }
            @Override
            public boolean onQueryTextChange(String query) {
                if (adapter != null) {
                    adapter.search(query);

                    if (adapter.getItemCount() == 0) showErrorFilmes(ErrorUtils.Type.NO_FILME_FOUND);
                    else binding.filmesFlipper.setDisplayedChild(2); // Filmes Content
                }

                return true;
            }
        });
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        binding.mainFlipper.setDisplayedChild(0); // Main Loading

        // Configurar layout da recycler-view
        binding.rvFilmes.setLayoutManager(new GridLayoutManager(getContext(), 3));

        // Carregar filmes
        loadFilmes(filter);

        // Swipe refresh
        binding.swipeRefresh.setOnRefreshListener(() -> {
            binding.swipeRefresh.setRefreshing(false);

            // Apenas limpar a cache de filmes se tiver internet
            if (ConnectionUtils.hasInternet(requireContext())) manager.clearCacheFilmes();

            // Carregar filmes
            loadFilmes(filter);
        });
    }

    private void loadFilmes(DataManager.FilterFilmes filter) {
        binding.filmesFlipper.setDisplayedChild(0); // Filmes Loading

        // Obter estado da ligação à internet
        boolean hasInternet = ConnectionUtils.hasInternet(requireContext());

        if (new PreferencesManager(requireContext()).getCinemaId() == -1) {
            showError(ErrorUtils.Type.INVALID_CINEMA);
            return;
        }

        // Obter filmes da API
        manager.getFilmes(requireContext(), filter, new FilmesListener() {
            @Override
            public void onSuccess(List<Filme> filmes) {
                setList(filmes);

                // Tem cache mas não tem internet
                if (!hasInternet) ErrorUtils.showToast(requireContext(), ErrorUtils.Type.NO_INTERNET);
            }
            @Override
            public void onInvalidCinema() {
                showError(ErrorUtils.Type.INVALID_CINEMA);
                manager.clearCacheFilmes();
            }
            @Override
            public void onError() {
                showError(hasInternet ? ErrorUtils.Type.API_ERROR : ErrorUtils.Type.NO_INTERNET);
                manager.clearCacheFilmes();
            }
        });
    }

    private void setList(List<Filme> filmes) {
        // Evitar crash ao sair do fragment
        if (binding == null || !isAdded()) return;

        // Se clicou num filme --> abrir detalhes
        adapter = new FilmesAdapter(filmes, filme -> {
            // Verificar ligação à internet
            if (!ConnectionUtils.hasInternet(requireContext())) {
                ErrorUtils.showToast(requireContext(), ErrorUtils.Type.NO_INTERNET);
                return;
            }

            Intent intent = new Intent(getActivity(), DetalhesFilmeActivity.class);
            intent.putExtra("filmeId", filme.getId());
            startActivity(intent);
        });

        binding.rvFilmes.setAdapter(adapter);
        binding.mainFlipper.setDisplayedChild(2); // Main Content
        binding.filmesFlipper.setDisplayedChild(2); // Filmes Content

        // Mostrar item de pesquisa
        showItemPesquisa(true);

        // Pesquisa
        if (searchView != null) {
            adapter.search(searchView.getQuery().toString());
        }

        // Configurar listeners dos filtros
        setOnFilterClickListeners();
    }

    private void setOnFilterClickListeners() {
        View.OnClickListener filterClickListener = v -> {
            // Limpar pesquisa
            clearSearch();

            binding.btnEmExibicao.setChecked(v.getId() == R.id.btnEmExibicao);
            binding.btnKids.setChecked(v.getId() == R.id.btnKids);
            binding.btnBrevemente.setChecked(v.getId() == R.id.btnBrevemente);

            if (v.getId() == R.id.btnEmExibicao) filter = DataManager.FilterFilmes.EM_EXIBICAO;
            else if (v.getId() == R.id.btnKids) filter = DataManager.FilterFilmes.KIDS;
            else if (v.getId() == R.id.btnBrevemente) filter = DataManager.FilterFilmes.BREVEMENTE;

            // Carregar filmes
            loadFilmes(filter);
        };

        binding.btnEmExibicao.setOnClickListener(filterClickListener);
        binding.btnKids.setOnClickListener(filterClickListener);
        binding.btnBrevemente.setOnClickListener(filterClickListener);
    }

    private void showError(ErrorUtils.Type type) {
        // Evitar crash ao sair do fragment
        if (binding == null || !isAdded()) return;

        binding.mainFlipper.setDisplayedChild(1); // Error
        ErrorUtils.showLayout(binding.mainError, type);

        // Esconder item de pesquisa
        showItemPesquisa(false);

        // Action do botão
        binding.mainError.btnAction.setOnClickListener(v -> {
            switch (type) {
                case NO_INTERNET:
                    loadFilmes(filter);
                    break;
                case API_ERROR:
                    startActivity(new Intent(requireContext(), ConfiguracoesActivity.class));
                    break;
                case INVALID_CINEMA:
                    ((MainActivity)requireActivity()).navigateToFragment(R.id.navCinemas);
                    break;
            }
        });
    }

    private void showErrorFilmes(ErrorUtils.Type type) {
        binding.filmesFlipper.setDisplayedChild(1);
        ErrorUtils.showLayout(binding.errorFilmes, type);
    }

    private void showItemPesquisa(boolean show) {
        hasFilmes = show;
        requireActivity().invalidateOptionsMenu();
    }

    private void clearSearch() {
        if (searchView != null) {
            searchView.setQuery("", false);
            searchView.clearFocus();
        }
        requireActivity().invalidateOptionsMenu();
    }

    @Override
    public void onResume() {
        super.onResume();

        // Carregar filmes se não tiver cache
        if (manager.getCacheFilmes(filter).isEmpty()) loadFilmes(filter);
        clearSearch();
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}