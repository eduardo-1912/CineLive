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
import android.widget.Toast;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.activities.ConfiguracoesActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.DetalhesFilmeActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.MainActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.FilmesAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentFilmesBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.FilmeListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.FilmesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorPage;

/**
 * A simple {@link Fragment} subclass.
 * Use the {@link FilmesFragment} factory method to
 * create an instance of this fragment.
 */
public class FilmesFragment extends Fragment {
    private FragmentFilmesBinding binding;
    private FilmesManager filmesManager;
    private FilmesAdapter adapter;
    private SearchView searchView;

    private boolean isFilmesLoaded;

    private List<Filme> filmesEmExibicao;
    private List<Filme> filmesKids;
    private List<Filme> filmesBrevemente;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentFilmesBinding.inflate(inflater, container, false);

        // Swipe refresh
        binding.swipeRefresh.setOnRefreshListener(() -> {
            binding.swipeRefresh.setRefreshing(false);
            load();
        });

        return binding.getRoot();
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);
    }

    @Override
    public void onCreateOptionsMenu(@NonNull Menu menu, @NonNull MenuInflater inflater) {
        super.onCreateOptionsMenu(menu, inflater);

        inflater.inflate(R.menu.menu_pesquisa, menu);
        MenuItem itemPesquisa = menu.findItem(R.id.itemPesquisa);

        // Apenas mostrar item pesquisa se tiver filmes carregados
        itemPesquisa.setVisible(isFilmesLoaded);

        // Obter SearchView
        searchView = (SearchView) itemPesquisa.getActionView();
        searchView.setQueryHint(getString(R.string.pesquisar_filmes));

        searchView.setOnQueryTextListener(new SearchView.OnQueryTextListener() {
            @Override
            public boolean onQueryTextSubmit(String query) {
                adapter.filtrar(query);
                return true;
            }
            @Override
            public boolean onQueryTextChange(String query) {
                adapter.filtrar(query);
                return true;
            }
        });
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        binding.rvFilmes.setLayoutManager(new GridLayoutManager(getContext(), 3));
        binding.btnEmExibicao.setChecked(true);

        View.OnClickListener filterClickListener = v -> {
            binding.btnEmExibicao.setChecked(v.getId() == R.id.btnEmExibicao);
            binding.btnKids.setChecked(v.getId() == R.id.btnKids);
            binding.btnBrevemente.setChecked(v.getId() == R.id.btnBrevemente);

            if (v.getId() == R.id.btnEmExibicao) {
                atualizarLista(filmesEmExibicao);
            }
            else if (v.getId() == R.id.btnKids) {
                atualizarLista(filmesKids);
            }
            else if (v.getId() == R.id.btnBrevemente) {
                atualizarLista(filmesBrevemente);
            }
        };

        binding.btnEmExibicao.setOnClickListener(filterClickListener);
        binding.btnKids.setOnClickListener(filterClickListener);
        binding.btnBrevemente.setOnClickListener(filterClickListener);

        filmesManager = FilmesManager.getInstance();

        load();
    }

    private void load() {
        binding.viewFlipper.setDisplayedChild(0); // Loading

        // Verificar se tem internet
        if (!ConnectionUtils.hasInternet(requireContext())) {
            // Se não tiver filmes carregados --> mostrar erro
            if (filmesManager.getFilmesEmExibicao().isEmpty()) {
                showError(ErrorPage.Type.INTERNET);
                return;
            }

            // Se tiver filmes carregados --> mostrar lista em cache
            Toast.makeText(requireActivity(), R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
            filmesEmExibicao = filmesManager.getFilmesEmExibicao();
            atualizarLista(filmesEmExibicao);
            return;
        }

        // Verificar se tem cinema selecionado
        if (new PreferencesManager(requireContext()).getCinemaId() == -1) {
            showError(ErrorPage.Type.CINEMA_INVALIDO);
            return;
        }

        // Obter filmes da API
        filmesManager.getFilmesEmExibicao(requireContext(), new FilmeListener() {
            @Override
            public void onFilmesLoaded(List<Filme> filmes) {
                isFilmesLoaded = true;
                requireActivity().invalidateOptionsMenu();
                filmesEmExibicao = filmes;
                atualizarLista(filmesEmExibicao);
            }
            @Override
            public void onInvalidCinema() {
                showError(ErrorPage.Type.CINEMA_INVALIDO);
            }
            @Override
            public void onError() {
                showError(ErrorPage.Type.API);
            }
        });
    }

    private void atualizarLista(List<Filme> lista) {
        if (binding == null || !isAdded()) return;

        binding.viewFlipper.setDisplayedChild(2); // Main

        adapter = new FilmesAdapter(lista, filme -> {
            Intent intent = new Intent(getActivity(), DetalhesFilmeActivity.class);
            intent.putExtra("filme_id", filme.getId());
            startActivity(intent);
        });

        binding.rvFilmes.setAdapter(adapter);

        if (searchView != null) {
            adapter.filtrar(searchView.getQuery().toString());
        }
    }

    private void showError(ErrorPage.Type type) {
        binding.viewFlipper.setDisplayedChild(1); // Error
        ErrorPage.showError(binding.error, type);

        // Action do botão
        binding.error.btnAction.setOnClickListener(v -> {
            switch (type) {
                case INTERNET:
                    load();
                    break;
                case API:
                    startActivity(new Intent(requireContext(), ConfiguracoesActivity.class));
                    break;
                case CINEMA_INVALIDO:
                    ((MainActivity)requireActivity()).navigateToFragment(R.id.navCinemas);
                    break;
            }
        });
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}