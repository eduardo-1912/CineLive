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

import java.util.Arrays;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.activities.DetalhesFilmeActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.FilmesAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentFilmesBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.NetworkUtils;

/**
 * A simple {@link Fragment} subclass.
 * Use the {@link FilmesFragment} factory method to
 * create an instance of this fragment.
 */
public class FilmesFragment extends Fragment {
    private FragmentFilmesBinding binding;

    private List<Filme> listaEmExibicao;
    private List<Filme> listaKids;
    private List<Filme> listaBrevemente;

    private FilmesAdapter adapter;
    private SearchView searchView;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentFilmesBinding.inflate(inflater, container, false);
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

        if (NetworkUtils.hasInternet(requireContext())) {
            inflater.inflate(R.menu.menu_pesquisa, menu);

            // Obter item
            MenuItem itemPesquisa = menu.findItem(R.id.itemPesquisa);

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
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {

        if (!NetworkUtils.hasInternet(requireContext())) {
            binding.viewFlipper.setDisplayedChild(1);
            return;
        }

        binding.viewFlipper.setDisplayedChild(0);

        listaEmExibicao = Arrays.asList(
            new Filme(1, "The Truman Show", "http://10.0.2.2/CineLive/Web/frontend/web/uploads/posters/poster_68fa080f7e03f.jpg"),
            new Filme(2, "The Social Network", "http://10.0.2.2/CineLive/Web/frontend/web/uploads/posters/poster_69032dee2ed44.jpg"),
            new Filme(3, "Carros 2", "http://10.0.2.2/CineLive/Web/frontend/web/uploads/posters/poster_6910b6ad1f9ea.jpg"),
            new Filme(4, "Inside Out 2", "http://10.0.2.2/CineLive/Web/frontend/web/uploads/posters/poster_6918b4c3cf56d.jpg")
        );

        listaKids = Arrays.asList(
            new Filme(3, "Carros 2", "http://10.0.2.2/CineLive/Web/frontend/web/uploads/posters/poster_6910b6ad1f9ea.jpg"),
            new Filme(4, "Inside Out 2", "http://10.0.2.2/CineLive/Web/frontend/web/uploads/posters/poster_6918b4c3cf56d.jpg")
        );

        listaBrevemente = Arrays.asList(
            new Filme(5, "Interstellar", "http://10.0.2.2/CineLive/Web/frontend/web/uploads/posters/poster_68fa01aecd6d2.jpg"),
            new Filme(6, "The Prestige", "http://10.0.2.2/CineLive/Web/frontend/web/uploads/posters/poster_6918b2d190384.jpg")
        );

        binding.rvFilmes.setLayoutManager(new GridLayoutManager(getContext(), 3));

        binding.btnEmExibicao.setChecked(true);
        atualizarLista(listaEmExibicao);

        View.OnClickListener filterClickListener = v -> {
            binding.btnEmExibicao.setChecked(v.getId() == R.id.btnEmExibicao);
            binding.btnKids.setChecked(v.getId() == R.id.btnKids);
            binding.btnBrevemente.setChecked(v.getId() == R.id.btnBrevemente);

            if (v.getId() == R.id.btnEmExibicao) {
                atualizarLista(listaEmExibicao);
            }
            else if (v.getId() == R.id.btnKids) {
                atualizarLista(listaKids);
            }
            else if (v.getId() == R.id.btnBrevemente) {
                atualizarLista(listaBrevemente);
            }
        };

        binding.btnEmExibicao.setOnClickListener(filterClickListener);
        binding.btnKids.setOnClickListener(filterClickListener);
        binding.btnBrevemente.setOnClickListener(filterClickListener);
    }

    private void atualizarLista(List<Filme> lista) {
        adapter = new FilmesAdapter(lista, filme -> {
            Intent intent = new Intent(getActivity(), DetalhesFilmeActivity.class);
            intent.putExtra("filme_id", filme.id);
            startActivity(intent);
        });

        binding.rvFilmes.setAdapter(adapter);

        if (searchView != null) {
            adapter.filtrar(searchView.getQuery().toString());
        }
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}